<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundNextHandlerContract;

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
 *   - call $next->handle($envelope) — pass task forward;
 *   - throw {@see OutboundRetryException} — retry with delay;
 *   - throw {@see OutboundSkipException} or {@see OutboundBusinessErrorException} — drop to DLQ.
 *
 * @see OutboundPipeline
 */
interface OutboundMiddleware
{
    public function handle(
        OutboundEnvelope $envelope,
        OutboundNextHandlerContract $next,
    ): void;
}
