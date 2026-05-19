<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRequestCorrelationContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueProducerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

final class TgBotApiDTOQueue
{
    public function __construct(
        private readonly QueueProducerContract $producer,
        private readonly TgRequestCorrelationContract $correlation,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    public function queue(
        string $token,
        TgApiMethodDTOContract $dto,
        ?TgRequestExecutionConfig $config = null,
    ): string {
        $config ??= new TgRequestExecutionConfig(
            mode: TgRequestExecutionConfig::MODE_ASYNC,
        );

        $requestDTO = $this->buildRequestDTO($token, $dto, $config);

        $this->producer->publish($requestDTO);

        $this->logger?->debug(
            sprintf(
                'Message %s queued: #%s method=%s',
                $dto->tgApiEntity()->value,
                $requestDTO->requestId,
                $dto->tgApiEntity()->name,
            ),
        );

        return $requestDTO->requestId;
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
}