<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

use BAGArt\TelegramBot\Exceptions\TgBotConfigurationException;

class TgBotConfig
{
    public readonly string $botId;

    public function __construct(
        public readonly string $token,
        ?string $botId = null,
    ) {
        if ($botId !== null) {
            $this->botId = $botId;
        } else {
            $tokenParts = explode(':', $this->token);

            if (!is_numeric($tokenParts[0]) || $tokenParts[0] < 100 || strlen($tokenParts[1]) != 35) {
                throw new TgBotConfigurationException('Invalid token');
            }
            $this->botId = $tokenParts[0];
        }
    }
}
