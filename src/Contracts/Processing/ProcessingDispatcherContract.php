<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

interface ProcessingDispatcherContract
{
    public const string TYPE = 'TYPE';

    /**
     * Dispatch a DTO through a pipeline of processors.
     *
     * @param  list<TgTypeDTOProcessorContract|class-string<TgTypeDTOProcessorContract>>  $processors
     */
    public function dispatch(
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        array $processors,
        ?string $action = null,
    ): int;
}
