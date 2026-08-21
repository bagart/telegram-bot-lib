<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers;

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

final class RedisQueueDTOProcessJob
{
    public function __construct(
        public readonly TgServiceConfig $serviceConfig,
        public readonly UpdateContext $context,
        public readonly ?TgApiTypeDTOContract $updateDto = null,
    ) {
    }

    public function handle(): void
    {
        // BotProcessorContext is not serializable, so a class-string processor
        // cannot be built in the queue worker. Dispatch a pre-built processor
        // instance or use a context-carrying dispatcher instead.
        throw new \LogicException(
            'RedisQueueDTOProcessJob cannot build processor '.$this->context->processor
            .' without a BotProcessorContext; this legacy payload path is not supported',
        );
    }
}
