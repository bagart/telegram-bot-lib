<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use Closure;

/**
 * Outbound pipeline middleware (PSR-15 style, synchronous, void return).
 *
 * Chain order (todo.md §3.2):
 *   ExpiryMiddleware → RetryBudgetMiddleware → RateLimitMiddleware
 *   → TelegramOutboundExecutor
 *
 * Ordering is now handled by the queue implementation (OutboundOrderingQueueContract),
 * not by middleware.
 *
 * Middleware decisions:
 *   - call $next($envelope) — pass task forward;
 *   - throw {@see OutboundRetryException} — retry with delay;
 *   - throw {@see OutboundSkipException} or {@see OutboundBusinessErrorException} — drop to DLQ.
 *
 * @see OutboundPipeline
 */
interface OutboundMiddleware
{
    /**
     * @param  Closure(OutboundEnvelope): void  $next  Next middleware / final executor.
     */
    public function handle(OutboundEnvelope $envelope, Closure $next): void;
}
