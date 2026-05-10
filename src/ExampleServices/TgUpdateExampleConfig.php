<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ExampleServices;

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;
use BAGArt\TelegramBot\ApiCommunication\Pollers;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\TgUpdateConfig;

class TgUpdateExampleConfig extends TgUpdateConfig
{
    public function __construct(
        public readonly TgBotConfig $bot,
        public string $poller = Pollers\AsyncPoller::TYPE,
        public string $dispatcher = Dispatchers\AsyncFiberDtoPipelineDispatcher::TYPE,
        public bool $show = false,
        public bool $noAck = false,
        public bool $log = false,
        public bool $echo = false,
        public bool $store = false,
    ) {
    }
}
