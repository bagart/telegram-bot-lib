<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers\LaravelQueueDispatcher;

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LaravelProcessingJob implements ShouldQueue
{
    use Queueable;

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
            'LaravelProcessingJob cannot build processor '.$this->context->processor
            .' without a BotProcessorContext; this legacy payload path is not supported',
        );
    }
}
