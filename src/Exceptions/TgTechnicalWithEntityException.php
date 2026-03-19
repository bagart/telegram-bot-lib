<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

class TgTechnicalWithEntityException extends TgTechnicalException
{
    public function __construct(
        public string $tgEntityName,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            "Technical error with $tgEntityName: $message",
            500,
            $previous,
        );
    }
}
