<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Exceptions;

class TgApiRateLimitException extends TgApiNetworkException
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
