<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

class TgApiUserBreakException extends TelegramBotException
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
