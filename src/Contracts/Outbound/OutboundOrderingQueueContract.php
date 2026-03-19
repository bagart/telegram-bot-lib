<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

/**
 * Extended queue contract that guarantees per-orderingKey ordering.
 *
 * - pop() never returns two concurrent OutboundEnvelope with the same orderingKey
 * - push() distributes tasks to per-orderingKey queues
 * - ack()/release() manage the lifecycle of ready_keys
 */
interface OutboundOrderingQueueContract extends OutboundQueueContract
{
    /**
     * Atomically grabs a free key from ready_keys (ZPOPMIN).
     * Returns orderingKey or null if no free keys.
     */
    public function lockNextReadyKey(): ?string;

    /**
     * Checks the queue state for orderingKey and returns the key
     * to ready_keys via LINDEX + conditional ZADD if tasks remain in the queue.
     */
    public function refreshKeyState(string $orderingKey): void;
}
