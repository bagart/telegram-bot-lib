<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

use Throwable;

class TgApiNetworkException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgEntityName,
        ?Throwable $previous = null,
        ?string $message = null,
    ) {
        parent::__construct(
            tgEntityName: $tgEntityName,
            message: $message
            ?? "Telegram Api Return Problem with $tgEntityName: ".$previous?->getMessage(),
            previous: $previous
        );
    }
}
