<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

use BAGArt\ASKClient\Contracts\RateLimiter\RateLimiterContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;

/**
 * Outbound rate limiter adapter over the {@see RateLimiterContract} kernel.
 *
 * Thin layer: direct delegation without key transformation. The kernel implementation
 * (TgAdvancedRateLimiter) parses the {botId}:{method}:{chatId} key format and builds
 * internal cache keys (tg_limit:{botId}:...). The pipeline builds the key via
 * RateLimitMiddleware::buildKey() and passes it here as an opaque string.
 *
 * registerRetryAfter is called from the Executor after 429 — fixes the "dead code" bug
 * (todo.md §0.5: the method existed but was never called).
 *
 * See todo.md §1.4, §3.6.
 */
final class OutboundRateLimiterAdapter implements OutboundRateLimiterContract
{
    public function __construct(
        private readonly RateLimiterContract $limiter,
    ) {
    }

    public function getRetryDelay(string $key): float
    {
        return $this->limiter->getRetryDelay($key);
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
        $this->limiter->registerRetryAfter($key, $seconds);
    }

    public function markSent(string $key): void
    {
        $this->limiter->markSent($key);
    }
}
