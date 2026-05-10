<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRequestCorrelationContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueConsumerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueProducerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalException;
use BAGArt\TelegramBot\Exceptions\TgQueueException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

final class QueuedTgBotApiDTOClient implements TgBotApiDTOClientContract
{
    private const int RESPONSE_POLL_INTERVAL_US = 50000;

    public function __construct(
        private readonly QueueProducerContract $producer,
        private readonly QueueConsumerContract $responseConsumer,
        private readonly TgRequestCorrelationContract $correlation,
        private readonly ?TgRequestExecutionConfig $defaultConfig = null,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    public static function build(
        ?TgBotCacheWrapper $cache = null,
        ?TgBotLogWrapper $logger = null,
        ?TgBotApiTransportContract $transport = null,
    ): static {
        throw new TgQueueException(
            'QueuedTgBotApiDTOClient requires explicit DI. Use constructor or container.'
        );
    }

    public function request(
        string $token,
        TgApiMethodDTOContract $dto,
    ): TgApiResponse {
        $config = $this->defaultConfig ?? new TgRequestExecutionConfig();

        return $this->requestWithConfig($token, $dto, $config);
    }

    public function requestWithConfig(
        string $token,
        TgApiMethodDTOContract $dto,
        ?TgRequestExecutionConfig $config = null,
    ): TgApiResponse {
        $config ??= $this->defaultConfig ?? new TgRequestExecutionConfig();

        $requestDTO = $this->buildRequestDTO($token, $dto, $config);

        $this->producer->publish($requestDTO);

        if ($config->mode === TgRequestExecutionConfig::MODE_ASYNC) {
            return new TgApiResponse(
                ok: true,
                possibleResultTypes: $dto::getReturnTypes(),
                result: $requestDTO->requestId,
            );
        }

        return $this->awaitResponse($requestDTO, $dto);
    }

    /**
     * IMPORTANT:
     * This async returns requestId immediately.
     *
     * Real async response processing must be handled
     * by a separate response daemon / consumer worker.
     *
     * Promise here must NOT busy-wait.
     */
    public function requestAsync(
        string $token,
        TgApiMethodDTOContract $dto,
        int $attempt = 1,
    ): PromiseInterface {
        $config = $this->defaultConfig ?? new TgRequestExecutionConfig(
            mode: TgRequestExecutionConfig::MODE_ASYNC
        );

        $requestDTO = $this->buildRequestDTO($token, $dto, $config);

        $this->producer->publish($requestDTO);

        $this->logger?->debug(
            sprintf(
                'Async request queued: requestId=%s method=%s',
                $requestDTO->requestId,
                $dto->tgApiEntity()->name,
            )
        );

        /**
         * Return immediately.
         *
         * Response must be consumed later by:
         * - daemon
         * - webhook
         * - callback worker
         * - event processor
         */
        return Create::promiseFor($requestDTO->requestId);
    }

    private function buildRequestDTO(
        string $token,
        TgApiMethodDTOContract $dto,
        TgRequestExecutionConfig $config,
    ): TgOutboundRequestDTO {
        $requestId = $this->correlation->generateRequestId();

        $responseQueue = $config->mode === TgRequestExecutionConfig::MODE_SYNC
            ? $this->correlation->generateResponseQueueByRequestId($requestId)
            : null;

        return new TgOutboundRequestDTO(
            requestId: $requestId,
            token: $token,
            dto: $dto,
            executionConfig: $config,
            responseQueue: $responseQueue,
            createdAt: time(),
        );
    }

    private function awaitResponse(
        TgOutboundRequestDTO $request,
        TgApiMethodDTOContract $dto,
    ): TgApiResponse {
        if ($request->responseQueue === null) {
            throw new TgBotTechnicalException(
                'Sync request requires responseQueue'
            );
        }

        $deadline = microtime(true) + $request->executionConfig->timeoutSeconds;

        while (microtime(true) < $deadline) {
            try {
                $response = $this->responseConsumer
                    ->consumeResponseQueue($request->responseQueue);

                if ($response !== null) {
                    return $this->convertResponse($response, $dto, $request);
                }
            } catch (\Throwable $e) {
                $this->logger?->error(
                    sprintf(
                        'Response consume failed: requestId=%s error=%s',
                        $request->requestId,
                        $e->getMessage(),
                    )
                );
            }

            usleep(self::RESPONSE_POLL_INTERVAL_US);
        }

        throw new TgBotTechnicalException(
            sprintf(
                'Response timeout: requestId=%s method=%s',
                $request->requestId,
                $dto->tgApiEntity()->name,
            )
        );
    }

    private function convertResponse(
        TgOutboundResponseDTO $response,
        TgApiMethodDTOContract $dto,
        TgOutboundRequestDTO $request,
    ): TgApiResponse {
        if (!$response->success) {
            return new TgApiResponse(
                ok: false,
                possibleResultTypes: $dto::getReturnTypes(),
                result: null,
                errorCode: $response->errorCode,
                retryAfter: $response->retryAfter,
            );
        }

        if ($response->result instanceof TgApiResponse) {
            return $response->result;
        }

        return new TgApiResponse(
            ok: true,
            possibleResultTypes: $dto::getReturnTypes(),
            result: $response->result,
        );
    }
}
