<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\Async;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;

interface DtoPipelineDispatcherContract
{
    public const string TYPE = 'TYPE';

    /**
     * Dispatch a DTO through a pipeline of processors.
     *
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processors
     */
    public function dispatch(
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        array $processors,
        ?string $action = null,
    ): int;
}
