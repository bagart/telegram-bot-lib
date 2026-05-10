<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class TgBotConfig
{
    public function __construct(
        public readonly string $token,
        public string $logLevel = TgBotLogWrapper::LEVEL_DEFAULT,
        public string $send = 'auto',
    ) {
    }
}
