<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

use BAGArt\TelegramBot\Contracts\Exceptions\TelegramBotException;
use RuntimeException;

class TgUnexpectedApiReturnException extends RuntimeException implements TelegramBotException
{
    public function __construct(
        public string $tgEntityName,
        public string|array $expectType,
        public mixed $response,
        ?string $message = null,
    ) {
        parent::__construct(
            ($message ?? "Unexpected Telegram Api $tgEntityName Return")
            .'Expect: '.json_encode($expectType).'; Response:'.json_encode($response),
            400,
        );
    }
}
