<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\ASKClient\Contracts\RateLimiter\RateLimiterContract;

/**
 * Outbound pipeline rate limiter contract.
 *
 * Thin facade-wrapper over the core {@see RateLimiterContract}
 * (implementation — TgAdvancedRateLimiter via OutboundRateLimiterAdapter).
 *
 * registerRetryAfter is called from Executor after 429 from Telegram — fixes
 * the "dead code" bug (method existed but was never called, todo.md §0.5).
 *
 * @see todo.md §1.4, §3.6.
 */
interface OutboundRateLimiterContract
{
    /**
     * Current delay before allowing the next send for a key.
     *
     * @return float Seconds; 0.0 = can send.
     */
    public function getRetryDelay(string $key): float;

    /**
     * Register retry_after received from Telegram (429).
     *
     * @param  float  $seconds  How many seconds to wait before next send.
     */
    public function registerRetryAfter(string $key, float $seconds): void;

    /**
     * Mark successful send (update internal limit counters).
     */
    public function markSent(string $key): void;
}
