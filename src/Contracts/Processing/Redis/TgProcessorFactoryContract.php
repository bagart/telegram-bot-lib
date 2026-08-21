<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Redis;

use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;

interface TgProcessorFactoryContract
{
    /**
     * @param  class-string<TgTypeDTOProcessorContract>  $processorClass
     */
    public function create(string $processorClass, BotProcessorContext $context): TgTypeDTOProcessorContract;
}
