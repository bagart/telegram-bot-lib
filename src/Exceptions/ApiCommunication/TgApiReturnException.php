<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

use BAGArt\TelegramBot\Http\Pure\TgApiResponse;

class TgApiReturnException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgMethodName,
        public readonly ?TgApiResponse $response = null,
        ?string $message = null,
    ) {
        parent::__construct(
            tgMethodName: $tgMethodName,
            message: ($message ?? "Telegram Api Return Problem with $tgMethodName")
            . ($response ? '; Response: '.json_encode($response) : null)
        );
    }
}
