<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgProcessorFactoryContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;

final class TgLocalProcessorFactory implements TgProcessorFactoryContract
{
    public function create(string $processorClass, BotProcessorContext $context): TgTypeDTOProcessorContract
    {
        return $processorClass::build($context);
    }
}
