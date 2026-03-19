<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;

class ProcessorConfig
{
    public function __construct(
        public readonly bool $echo = false,
        public readonly bool $show = false,
        public readonly bool $log = false,
        public readonly bool $store = false,
        public readonly bool $dbg = false,
        public readonly bool $antispam = false,

        /** @var list<class-string<TgTypeDTOProcessorContract>, array> */
        public array $options = [],
    ) {
    }
}
