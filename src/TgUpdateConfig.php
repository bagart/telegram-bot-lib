<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;
use BAGArt\TelegramBot\ApiCommunication\Pollers;

class TgUpdateConfig
{
    public function __construct(
        public string $token,
        public string $poller = Pollers\AsyncPoller::TYPE,
        public string $dispatcher = Dispatchers\AsyncFiberDtoPipelineDispatcher::TYPE,
        public bool $show = false,
        public bool $dbg = false,
        public bool $noAck = false,
        public bool $log = false,
    ) {
    }
}
