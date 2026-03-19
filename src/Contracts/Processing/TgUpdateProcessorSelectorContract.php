<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use Generator;

interface TgUpdateProcessorSelectorContract
{
    /**
     * @return Generator<TgTypeDTOProcessorContract>|TgTypeDTOProcessorContract[]
     */
    public function selectProcessors(
        UpdateTypeDTO $updateDTO,
        TgBotConfig $botConfig,
    ): Generator|array;
}
