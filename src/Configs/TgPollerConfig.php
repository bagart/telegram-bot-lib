<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

class TgPollerConfig
{
    /**
     * Platform-wide default update subscription (ADR-002 layer 1): the
     * chat_member / my_chat_member types keep mirrored Telegram adminship
     * (T1) real-time in both long-polling and webhook setups.
     *
     * @var list<string>
     */
    public const DEFAULT_ALLOWED_UPDATES = [
        'message',
        'callback_query',
        'edited_channel_post',
        'chat_member',
        'my_chat_member',
    ];

    /**
     * @param  string[]  $allowedUpdates
     */
    public function __construct(
        public bool $noAck = false,
        public int $limit = 100,
        public int $timeout = 10,
        #TURBO mode
        public int $allowedMaxInboxSizeToPoll = 0,
        public array $allowedUpdates = self::DEFAULT_ALLOWED_UPDATES,
    ) {
    }
}
