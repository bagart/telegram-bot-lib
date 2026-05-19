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
        TgBotConfig $bot,
        string $poller = Pollers\AsyncPoller::TYPE,
        string $dispatcher = Dispatchers\AsyncFiberDtoPipelineDispatcher::TYPE,
        bool $show = false,
        bool $noAck = false,
        bool $log = false,
        public bool $echo = false,
        public bool $store = false,
    ) {
        parent::__construct(
            bot: $bot,
            poller: $poller,
            dispatcher: $dispatcher,
            show: $show,
            noAck: $noAck,
            log: $log,
        );
    }
}
