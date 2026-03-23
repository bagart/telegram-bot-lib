<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\TgApi;

/**
 * Exception thrown when Telegram returns a 429 Too Many Requests error.
 */
class TgFloodWaitException extends TgApiException
{
    private int $retryAfter;

    public function __construct(
        int $retryAfter,
        string $message = "Too many requests. Please wait.",
        ?int $errorCode = 429,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
