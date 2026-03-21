<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use RuntimeException;

/**
 * Thrown when a bot token or webhook secret has invalid format.
 *
 * Expected formats:
 * - Token: `botId:tokenPart` (e.g. `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)
 * - Secret: `botId:hash` (e.g. `123456789:sha256...`)
 *
 * @see \BAGArt\TelegramBot\BotServices\AutoSecretByTokenService
 */
class TgBotInvalidSecretException extends RuntimeException implements TelegramBotException
{
}
