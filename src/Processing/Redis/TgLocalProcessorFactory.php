<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgProcessorFactoryContract;

final class TgLocalProcessorFactory implements TgProcessorFactoryContract
{
    public function create(string $processorClass, TgServiceConfig $serviceConfig): TgTypeDTOProcessorContract
    {
        return $processorClass::build($serviceConfig);
    }
}
