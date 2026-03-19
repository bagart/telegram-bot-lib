<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Infrastructure;

/**
 * Rate limiting contract for Telegram API requests.
 *
 * @see https://core.telegram.org/bots/api#rate-limits
 */
interface TgRateLimiterContract
{
    /**
     * Acquire tokens for the given key.
     */
    public function acquire(string $key, int $tokens = 1): bool;

    /**
     * Get available tokens for the given key.
     */
    public function available(string $key): int;

    /**
     * Reset the rate limit for the given key.
     */
    public function reset(string $key): void;
}
