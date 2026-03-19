<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\ASKClient\Retry\RetryPolicy;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;
use Throwable;

class TgRetryPolicy extends RetryPolicy implements TgRetryPolicyContract
{
    /**
     * Telegram-specific: skip retries for long-polling getUpdates.
     */
    public function shouldRetry(
        string $method,
        int $attempt,
        Throwable $error,
    ): bool {
        if ($method === 'getUpdates') {
            return false;
        }

        return parent::shouldRetry($method, $attempt, $error);
    }

    /**
     * Telegram-specific: accept TG exception types as retryable.
     */
    protected function isRetryableException(Throwable $error): bool
    {
        return $error instanceof TgApiNetworkException
            || $error instanceof TgApiRateLimitException
            || parent::isRetryableException($error);
    }
}
