<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgIntegration;

use InvalidArgumentException;

class BotSecretDTO
{
    private readonly string $token;

    private readonly ?string $secret;

    public function __construct(
        string $token,
        ?string $secret = null,
    ) {
        $this->validate($token);
        $this->token = $token;
        $this->secret = $secret;
    }

    public function botId(): string
    {
        return explode(':', $this->token)[0];
    }

    public function token(): string
    {
        return $this->token;
    }

    public function secret(): ?string
    {
        return $this->secret;
    }

    /**
     * Protect secrets
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * Protect secrets
     */
    public function toJson(): string
    {
        return '{}';
    }

    private function validate(string $token): void
    {
        if (! preg_match('/^\d{5,20}:[A-Za-z0-9_-]+$/', $token)) {
            throw new InvalidArgumentException(
                'Invalid token format. Expected: {numeric_id}:{secret}'
            );
        }
    }
}
