<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRequestOrderingContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueConsumerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueProducerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiReturnException;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalException;
use BAGArt\TelegramBot\Exceptions\TgQueueException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use Throwable;

final class TgOutboundDaemon
{
    use DaemonTrait;

    private const string RATE_LIMIT_KEY_TPL = 'tg_bot_%s_%s';

    private const float ORDERING_LOCK_RETRY_DELAY = 0.5;

    private const float RATE_LIMIT_RETRY_DELAY = 1.0;

    public function __construct(
        private readonly QueueConsumerContract $consumer,
        private readonly QueueProducerContract $producer,
        SchedulerContract $scheduler,
        private readonly TgBotApiDTOClientContract $dtoClient,
        private readonly TgRequestOrderingContract $ordering,
        private readonly TgRateLimiterContract $rateLimiter,
        private readonly TgRetryPolicyContract $retryPolicy,
        ?TgBotLogWrapper $logger = null,
    ) {
        $this->initDaemon($scheduler, $logger);
    }

    protected function getLogPrefix(): string
    {
        return 'TgOutboundDaemon';
    }

    protected function onStart(): void
    {
        $this->consumer->connect();
        $this->producer->connect();
    }

    protected function tryConsume(): mixed
    {
        return $this->consumer->consume();
    }

    protected function dispatch(mixed $item): void
    {
        if (!$item instanceof TgOutboundRequestDTO) {
            return;
        }

        ++$this->concurrentFibers;

        $daemon = $this;

        $fiber = new Fiber(function () use ($item, $daemon): void {
            $responseDTO = null;

            try {
                $daemon->acquireOrderingLock($item);

                try {
                    $apiResponse = $daemon->executeWithRetry($item);
                } finally {
                    $daemon->ordering->release($item);
                }

                $responseDTO = new TgOutboundResponseDTO(
                    requestId: $item->requestId,
                    success: true,
                    result: $apiResponse,
                    responseQueue: $item->responseQueue,
                    completedAt: time(),
                );

                ++$daemon->totalProcessed;

                $daemon->logger?->debug(
                    sprintf(
                        'Request completed: method=%s requestId=%s',
                        $item->dto->tgApiEntity()->name,
                        $item->requestId,
                    ),
                );
            } catch (Throwable $e) {
                $responseDTO = new TgOutboundResponseDTO(
                    requestId: $item->requestId,
                    success: false,
                    result: null,
                    error: $e->getMessage(),
                    errorCode: $e->getCode() !== 0 ? $e->getCode() : null,
                    retryAfter: $daemon->extractRetryAfter($e),
                    responseQueue: $item->responseQueue,
                    completedAt: time(),
                );

                ++$daemon->totalErrors;

                $daemon->logger?->error(
                    sprintf(
                        'Request failed after retries: method=%s requestId=%s error=%s',
                        $item->dto->tgApiEntity()->name,
                        $item->requestId,
                        $e->getMessage(),
                    ),
                    [
                        'exception' => $e::class,
                        'request_id' => $item->requestId,
                    ],
                );
            } finally {
                --$daemon->concurrentFibers;
            }

            if ($responseDTO !== null) {
                try {
                    $daemon->producer->publishResponse($responseDTO);
                } catch (Throwable $e) {
                    $daemon->logger?->error(
                        sprintf(
                            'Failed to publish response: requestId=%s error=%s',
                            $item->requestId,
                            $e->getMessage(),
                        ),
                        ['exception' => $e::class],
                    );
                }
            }
        });

        $this->scheduler->enqueue($fiber);
    }

    private function executeWithRetry(TgOutboundRequestDTO $request): TgApiResponse
    {
        $maxAttempts = $request->executionConfig->maxRetryAttempts + 1;

        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            $this->acquireRateLimit($request);

            try {
                $this->logger?->debug(
                    sprintf(
                        'Executing request: method=%s requestId=%s',
                        $request->dto->tgApiEntity()->name,
                        $request->requestId,
                    ),
                );

                $promise = $this->dtoClient->requestAsync(
                    token: $request->token,
                    dto: $request->dto,
                );

                $apiResponse = $this->scheduler->await($promise);

                if (!$apiResponse instanceof TgApiResponse) {
                    throw new TgApiReturnException(
                        tgEntityName: $request->dto->tgApiEntity()->name,
                        response: $apiResponse,
                    );
                }

                if (!$apiResponse->ok) {
                    if ($this->handleErrorResponse($request, $apiResponse, $attempt, $maxAttempts)) {
                        continue;
                    }
                    throw new TgApiReturnException(
                        tgEntityName: $request->dto->tgApiEntity()->name,
                        response: $apiResponse
                    );
                }

                return $apiResponse;
            } catch (Throwable $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                if (!$this->retryPolicy->shouldRetry(
                    $request->dto->tgApiEntity()->name,
                    $attempt,
                    $e,
                )) {
                    throw $e;
                }

                $delay = $this->retryPolicy->getDelay($attempt);

                $this->logger?->debug(
                    sprintf(
                        'Retry attempt %d/%d for method=%s requestId=%s delay=%ds error=%s',
                        $attempt,
                        $maxAttempts,
                        $request->dto->tgApiEntity()->name,
                        $request->requestId,
                        $delay,
                        $e->getMessage(),
                    ),
                );

                $this->parkWithDelay((float) $delay);
            }
        }

        throw new TgBotTechnicalException(
            sprintf(
                'Max retry attempts exceeded for method=%s requestId=%s',
                $request->dto->tgApiEntity()->name,
                $request->requestId,
            ),
        );
    }

    private function handleErrorResponse(
        TgOutboundRequestDTO $request,
        TgApiResponse $apiResponse,
        int $attempt,
        int $maxAttempts,
    ): bool {
        $errorCode = $apiResponse->errorCode;

        if ($errorCode === 429) {
            $retryAfter = $apiResponse->retryAfter ?? 1;

            if ($attempt >= $maxAttempts) {
                return false;
            }

            $this->logger?->warning(
                sprintf(
                    'Rate limited (429): method=%s requestId=%s attempt=%d/%d retry_after=%d',
                    $request->dto->tgApiEntity()->name,
                    $request->requestId,
                    $attempt,
                    $maxAttempts,
                    $retryAfter,
                ),
            );

            $this->parkWithDelay((float) max(1, $retryAfter));

            return true;
        }

        return false;
    }

    private function acquireRateLimit(TgOutboundRequestDTO $request): void
    {
        $rateKey = sprintf(
            self::RATE_LIMIT_KEY_TPL,
            sha1($request->token),
            $request->dto->tgApiEntity()->name,
        );

        while (!$this->rateLimiter->acquire($rateKey)) {
            $this->logger?->debug(
                sprintf(
                    'Rate limit wait: method=%s requestId=%s key=%s',
                    $request->dto->tgApiEntity()->name,
                    $request->requestId,
                    $rateKey,
                ),
            );

            $this->parkWithDelay(self::RATE_LIMIT_RETRY_DELAY);
        }
    }

    private function acquireOrderingLock(TgOutboundRequestDTO $request): void
    {
        if (!$this->ordering->shouldWait($request)) {
            return;
        }

        while (!$this->ordering->acquire($request)) {
            $this->logger?->debug(
                sprintf(
                    'Ordering lock wait: requestId=%s key=%s',
                    $request->requestId,
                    $request->executionConfig->orderingKey
                    ?? 'bot:' . sha1($request->token),
                ),
            );

            $this->parkWithDelay(self::ORDERING_LOCK_RETRY_DELAY);
        }
    }

    private function extractRetryAfter(Throwable $e): ?int
    {
        if (method_exists($e, 'getRetryAfter')) {
            $retryAfter = $e->getRetryAfter();

            if (is_int($retryAfter) && $retryAfter > 0) {
                return $retryAfter;
            }
        }

        $message = $e->getMessage();

        if (preg_match('/retry after (\d+)/i', $message, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
