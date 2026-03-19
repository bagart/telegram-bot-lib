<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

use BAGArt\TelegramBot\Exceptions\TelegramBotException;
use Throwable;

class TgApiCommunicationException extends TelegramBotException
{
    public function __construct(
        public string $tgMethodName,
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: ($message ?? "Telegram Api Return Problem with $tgMethodName"),
            code: 400,
            previous: $previous,
        );
    }
}
