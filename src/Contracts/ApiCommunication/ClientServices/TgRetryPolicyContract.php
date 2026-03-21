<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

use Throwable;

/**
 * Retry policy for failed Telegram API calls.
 */
interface TgRetryPolicyContract
{
    /**
     * Determine if the request should be retried.
     *
     * @see https://core.telegram.org/bots/api#making-requests
     */
    public function shouldRetry(string $method, int $attempt, ?Throwable $error = null): bool;

    /**
     * Get delay in seconds before next retry attempt.
     */
    public function getDelay(int $attempt): int;

    /**
     * Get maximum number of retry attempts.
     */
    public function getMaxAttempts(): int;
}
