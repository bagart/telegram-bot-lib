<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

use BAGArt\TelegramBot\Contracts\Queue\QueueConfigContract;

final class DaemonRuntime
{
    public const string MODE_ASYNC = 'async';

    public const string MODE_QUEUE = 'queue';

    public function __construct(
        public string $scheduler = self::MODE_ASYNC,
        public ?QueueConfigContract $queue = null,
    ) {
    }
}
