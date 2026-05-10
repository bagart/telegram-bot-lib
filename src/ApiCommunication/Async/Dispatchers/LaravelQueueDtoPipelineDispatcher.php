<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;

class LaravelQueueDtoPipelineDispatcher implements DtoPipelineDispatcherContract
{
    public const string TYPE = 'queue';

    /**
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processors
     */
    public function dispatch(
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        array $processors,
        ?string $action = null,
    ): int {
        $i = 0;
        foreach ($processors as $processor) {
            $job = new LaravelQueueDTOProcessJob(
                config: $config,
                processor: is_string($processor) ? $processor : $processor::class,
                botId: $botId,
                dto: $dto,
                action: $action,
            );
            $job::dispatch();
            ++$i;
        }

        return $i;
    }
}
