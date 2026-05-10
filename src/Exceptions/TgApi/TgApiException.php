<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\TgApi;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use Exception;

/**
 * Base exception for all Telegram API errors.
 */
class TgApiException extends Exception implements TelegramBotException
{
    public function __construct(
        string $message = "",
        ?int $code = null,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, $code ?? 0, $previous);
    }
}
