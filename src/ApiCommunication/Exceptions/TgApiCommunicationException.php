<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use RuntimeException;
use Throwable;

class TgApiCommunicationException extends RuntimeException implements TelegramBotException
{
    public function __construct(
        public string $tgEntityName,
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: ($message ?? "Telegram Api Return Problem with $tgEntityName"),
            code: 400,
            previous: $previous,
        );
    }
}
