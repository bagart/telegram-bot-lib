<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Exceptions;

use BAGArt\TelegramBot\TgApiServices\TgApiResponse;

class TgApiReturnException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgEntityName,
        public readonly TgApiResponse $response,
        ?string $message = null,
    ) {
        parent::__construct(
            tgEntityName: $tgEntityName,
            message: ($message ?? "Telegram Api Return Problem with $tgEntityName")
            .'; Response: '.json_encode($response),
        );
    }
}
