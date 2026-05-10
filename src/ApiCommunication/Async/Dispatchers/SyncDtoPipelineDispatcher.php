<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class SyncDtoPipelineDispatcher implements DtoPipelineDispatcherContract
{
    public const string TYPE = 'sync';

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
        $logger = TgBotLogWrapper::build();
        $i = 0;
        /** @var TgTypeDTOProcessorContract $processor */
        foreach ($processors as $processor) {
            ++$i;
            $instance = is_string($processor) ? $processor::build($config) : $processor;
            try {
                $instance->process($dto, $botId, $config, $action);
            } catch (\Throwable $e) {
                $logger->error('SyncFiberDtoPipelineDispatcher processDto '.$e::class.": {$e->getMessage()}");
            }
        }

        return $i;
    }
}
