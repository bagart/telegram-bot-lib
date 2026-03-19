<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use Closure;

/**
 * Task expiry checking middleware (todo.md §3.3, §5.3 timeout).
 *
 * Task older than $maxAgeSec seconds AND with >= $minAttemptsForExpiry attempts
 * is considered hopeless (expired) → {@see OutboundSkipException} → DLQ.
 *
 * Rationale: a new task might be old (sat in queue for a long time), but we give it
 * at least 2 attempts before dropping — to avoid immediate DLQ for
 * tasks that were merely waiting in the delayed queue.
 *
 * First middleware in the chain (todo.md §3.2).
 */
final class ExpiryMiddleware implements OutboundMiddleware
{
    public function __construct(
        private readonly int $maxAgeSec = 3600,
        private readonly int $minAttemptsForExpiry = 2,
        private readonly ?ASKClockContract $clock = null,
    ) {
    }

    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        $now = $this->clock?->time() ?? time();
        $age = $envelope->task->age($now);

        if ($age > $this->maxAgeSec && $envelope->state->getAttempt() >= $this->minAttemptsForExpiry) {
            throw new OutboundSkipException('expired');
        }

        $next($envelope);
    }
}
