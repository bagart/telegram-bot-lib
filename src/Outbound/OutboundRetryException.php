<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use RuntimeException;
use Throwable;

/**
 * Control flow exception: task should be retried with a delay.
 *
 * Thrown by middleware (RateLimit, Ordering, Executor) and caught by Worker.
 * Worker moves the task to the delayed queue for $delaySec seconds with $reason.
 *
 * IMPORTANT: this is a pipeline control flow exception, not a business error —
 * the task does NOT go to DLQ but returns to the queue.
 */
final class OutboundRetryException extends RuntimeException
{
    /**
     * @param  int  $delaySec  Delay before retry (seconds).
     * @param  string  $reason  Retry reason (for metrics: rate_limit / network_timeout …).
     * @param  Throwable|null  $previous  Root cause (e.g. TgApiRateLimitException).
     */
    public function __construct(
        public readonly int $delaySec,
        public readonly string $reason,
        ?Throwable $previous = null,
    ) {
        parent::__construct("Retry: {$this->reason} (delay {$this->delaySec}s)", 0, $previous);
    }
}
