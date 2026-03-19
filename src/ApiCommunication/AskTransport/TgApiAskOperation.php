<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

use BAGArt\TelegramBot\Configs\TgBotConfig;

/**
 * Operation object for the ASKClient execution pipeline.
 *
 * Carries everything needed to build a Telegram Bot API HTTP request.
 */
final class TgApiAskOperation
{
    public function __construct(
        public readonly TgBotConfig $config,
        public readonly string $method,
        public readonly array $params = [],
        public readonly ?int $timeout = null,
    ) {
    }
}
