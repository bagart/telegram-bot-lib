<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

class TgApiRateLimitException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgEntityName,
        ?string $message = null,
    ) {
        parent::__construct(
            tgEntityName: $tgEntityName,
            message: $message ?? "Rate limit exceeded for {$tgEntityName}"
        );
    }
}
