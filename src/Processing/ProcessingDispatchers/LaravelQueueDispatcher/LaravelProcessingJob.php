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
        $this->context->processor::build($this->serviceConfig)
            ->process(
                dto: $this->context->dto,
                botConfig: $this->context->botConfig,
                action: $this->context->source,
                updateDto: $this->updateDto,
            );
    }
}
