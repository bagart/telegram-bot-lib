<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use Closure;

/**
 * Attempt limit middleware (todo.md §3.3, §5.4 max attempts).
 *
 * If attempts >= $maxAttempts — the task exhausted its retry budget and
 * is discarded as hopeless → {@see OutboundSkipException} → DLQ.
 *
 * Placed AFTER Expiry: if a task has already expired — no point wasting retry
 * attempts on it.
 */
final class RetryBudgetMiddleware implements OutboundMiddleware
{
    public function __construct(
        private readonly int $maxAttempts = 5,
    ) {
    }

    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        if ($envelope->state->getAttempt() >= $this->maxAttempts) {
            throw new OutboundSkipException('max_attempts');
        }

        $next($envelope);
    }
}
