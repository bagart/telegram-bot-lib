<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Redis;

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;

interface TgProcessorFactoryContract
{
    /**
     * @param class-string<TgTypeDTOProcessorContract> $processorClass
     */
    public function create(string $processorClass, TgServiceConfig $serviceConfig): TgTypeDTOProcessorContract;
}
