<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException as TelegramBotExceptionInterface;

class TelegramBotException extends \RuntimeException implements TelegramBotExceptionInterface
{
}
