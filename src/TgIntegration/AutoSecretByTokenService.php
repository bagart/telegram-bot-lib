<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgIntegration;

use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;

class AutoSecretByTokenService implements TgBotsSecretServiceContract
{
    public function secret(string $token): string
    {
        if (! str_contains($token, ':')) {
            throw new TgBotInvalidSecretException('Invalid token format: expected "botId:tokenPart"');
        }

        [$botId, $tokenPart] = explode(':', $token);
        if (! is_numeric($botId) || empty($tokenPart)) {
            throw new TgBotInvalidSecretException('Invalid token format: expected "botId:tokenPart"');
        }

        return $botId.':'.hash('sha256', $tokenPart);
    }

    /**
     * @throws TgBotInvalidSecretException
     */
    public function botId(?string $secret): string
    {
        if ($secret === null || ! str_contains($secret, ':')) {
            throw new TgBotInvalidSecretException('Invalid secret format: expected "botId:hash"');
        }

        [$botId, $secretPart] = explode(':', $secret);
        if (! is_numeric($botId) || empty($secretPart)) {
            throw new TgBotInvalidSecretException('Invalid auto-secret format: 12345789:sha256 expected');
        }

        return $botId;
    }
}
