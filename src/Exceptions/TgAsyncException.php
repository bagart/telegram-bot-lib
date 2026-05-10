<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use RuntimeException;

class TgAsyncException extends RuntimeException implements TelegramBotException
{
}
