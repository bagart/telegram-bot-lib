<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

use BAGArt\TelegramBot\Exceptions\TgApi\TgApiException;
use Throwable;

class TgApiCommunicationException extends TgApiException
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
