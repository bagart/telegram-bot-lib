<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Outbound\Ordering\OrderingStrategyContract;

final class TgSender implements TgSenderContract
{
    public function __construct(
        private readonly OutboundQueueContract $queue,
        private readonly TgApiDTOMapperContract $dtoMapper,
        private readonly OrderingStrategyContract $orderingStrategy,
    ) {
    }

    public function send(TgBotConfig $botConfig, TgApiMethodDTOContract $dto): void
    {
        $dtoData = $this->dtoMapper->toArray($dto);

        $task = new OutboundTask(
            id: bin2hex(random_bytes(16)),
            botConfig: $botConfig,
            dtoClass: $dto::class,
            dtoData: $dtoData,
            orderingKey: $this->orderingStrategy->keyForDto($dtoData),
        );

        $this->queue->push($task);
    }
}
