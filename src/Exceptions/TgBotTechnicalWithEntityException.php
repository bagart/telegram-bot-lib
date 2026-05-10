<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

class TgBotTechnicalWithEntityException extends TgBotTechnicalException
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
