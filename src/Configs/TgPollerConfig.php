<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

class TgPollerConfig
{
    /**
     * @param  string[]  $allowedUpdates
     */
    public function __construct(
        public bool $noAck = false,
        public int $limit = 100,
        public int $timeout = 10,
        #TURBO mode
        public int $allowedMaxInboxSizeToPoll = 0,
        public array $allowedUpdates = ['message', 'callback_query', 'edited_channel_post'],
    ) {
    }
}
