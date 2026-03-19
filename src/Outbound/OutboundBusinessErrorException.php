<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use RuntimeException;
use Throwable;

/**
 * Control flow exception: business error, retry is pointless.
 *
 * Thrown by Executor for 400/401/403/404 and on lease_expired.
 * Worker moves the task to DLQ with the given reason and context.
 *
 * Context must NOT contain sensitive data (bot token, full payload) —
 * only a brief description for later diagnostics.
 *
 * @param  array<string,mixed>  $context
 */
final class OutboundBusinessErrorException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct("Business error: {$this->reason}", 0, $previous);
    }
}
