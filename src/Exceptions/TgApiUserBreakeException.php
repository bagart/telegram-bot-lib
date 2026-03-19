<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;

class TgApiUserBreakeException extends \RuntimeException implements TelegramBotException
{
    public function __construct(
        public string $tgEntityName,
        string $message = 'Manual break by User',
    ) {
        parent::__construct(
            message: $message,
            code: 499,
        );
    }
}
