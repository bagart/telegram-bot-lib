<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use RuntimeException;

class TgTransportException extends RuntimeException implements TelegramBotException
{
}
