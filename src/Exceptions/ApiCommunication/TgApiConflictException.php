<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

use Throwable;

class TgApiConflictException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgMethodName,
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        $msg = $message ?? "Conflict: terminated by other getUpdates request; make sure that only one bot instance is running.";
        parent::__construct(
            tgMethodName: $tgMethodName,
            message: $msg,
            previous: $previous
        );
    }
}
