<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers\LaravelQueueDispatcher;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

class LaravelProcessingDispatcher implements ProcessingDispatcherContract
{
    public const string TYPE = 'queue';

    public function dispatch(
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        array $processors,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): int {
        $i = 0;
        foreach ($processors as $processor) {
            $context = new UpdateContext(
                dto: $dto,
                processor: is_string($processor) ? $processor : $processor::class,
                botConfig: $botConfig,
                executionKey: null,
                source: $action,
            );

            $job = new LaravelProcessingJob(
                serviceConfig: $serviceConfig,
                context: $context,
                updateDto: $updateDto,
            );
            $job::dispatch();
            ++$i;
        }

        return $i;
    }
}
