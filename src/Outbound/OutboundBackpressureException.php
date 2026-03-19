<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use RuntimeException;

/**
 * Queue is full (backpressure).
 *
 * Thrown by InMemoryOutboundQueueContractContractContractContract when maxSize is exceeded — so the caller (processor
 * via TgSender) can react (delay sending, notify the user) rather than silently
 * accumulating tasks in memory until OOM.
 *
 * For Redis queue, backpressure is softer: queue_depth_high metric instead of throw
 * (todo.md §13 open question 2). This exception is for the bounded in-memory adapter.
 *
 * See todo.md §5.2, §7.3 (Memory management).
 */
final class OutboundBackpressureException extends RuntimeException
{
    public function __construct(string $queueName, int $size, int $maxSize)
    {
        parent::__construct(
            "Outbound queue '{$queueName}' is full: {$size}/{$maxSize} (backpressure).",
        );
    }
}
