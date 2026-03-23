<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use Throwable;

class TgRetryPolicy implements TgRetryPolicyContract
{
    private const MAX_ATTEMPTS = 3;

    private const BASE_DELAY_MS = 1000;

    public function shouldRetry(string $method, int $attempt, ?Throwable $error = null): bool
    {
        if ($attempt >= self::MAX_ATTEMPTS) {
            return false;
        }

        if ($error === null) {
            return false;
        }

        $message = $error->getMessage();

        if (str_contains($message, 'Timed out') || str_contains($message, 'cURL error 28')) {
            return true;
        }

        if (str_contains($message, 'rate limit')) {
            return true;
        }

        if (str_contains($message, '429')) {
            return true;
        }

        return false;
    }

    public function getDelay(int $attempt): int
    {
        $delay = self::BASE_DELAY_MS * (2 ** ($attempt - 1));

        return (int)($delay / 1000);
    }

    public function getMaxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }
}
