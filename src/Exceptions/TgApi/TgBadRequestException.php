<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\TgApi;

use Throwable;

/**
 * Exception thrown when the request is malformed or parameters are invalid (400 Bad Request).
 */
class TgBadRequestException extends TgApiException
{
    public function __construct(
        string $message = "Bad Request",
        ?int $errorCode = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, $previous);
    }
}
