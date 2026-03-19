<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

use Throwable;

class TgApiNetworkException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgMethodName,
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            tgMethodName: $tgMethodName,
            message: $message
            ?? "Telegram Api Return Problem with $tgMethodName: ".$previous?->getMessage(),
            previous: $previous
        );
    }
}
