<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use RuntimeException;

class TgBotTechnicalException extends RuntimeException implements TelegramBotException
{
    public function __construct(
        public string $tgEntityName,
        string $message,
    ) {
        parent::__construct(
            "Technical error with $tgEntityName: $message",
            500
        );
    }
}
