<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use RuntimeException;

/**
 * Control flow exception: task is hopeless, discarded as a business error.
 *
 * Thrown by ExpiryMiddleware (age > 1h && attempt >= 2) and RetryBudgetMiddleware
 * (attempt >= maxAttempts). Semantically equivalent to {@see OutboundBusinessErrorException},
 * but with a simplified signature (no context) — used only for predictive
 * skip checks where detailed context is not needed.
 *
 * Worker catches this exception and moves the task to DLQ with the given reason.
 */
final class OutboundSkipException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct($this->reason);
    }
}
