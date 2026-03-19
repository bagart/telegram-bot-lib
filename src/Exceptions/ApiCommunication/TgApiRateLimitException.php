<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication;

class TgApiRateLimitException extends TgApiCommunicationException
{
    public function __construct(
        public string $tgMethodName,
        ?string $message = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct(
            tgMethodName: $tgMethodName,
            message: $message ?? "Rate limit exceeded for {$tgMethodName}",
        );
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
