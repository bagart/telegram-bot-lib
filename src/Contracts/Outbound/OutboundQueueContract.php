<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;

/**
 * Base outbound queue contract. Implemented by all brokers (Redis, in-memory,
 * Laravel queue, SQS, RabbitMQ …).
 *
 * Unified typed signature: push(OutboundTask), pop(): ?OutboundEnvelope —
 * no string queue-names in base contract (channel is fixed = tg-outbound).
 *
 * Extended features (lease renewal, DLQ, channel discovery, purge) are extracted into
 * optional capability interfaces; components check them via instanceof
 * and use fallback if the capability is not supported by the broker.
 *
 * @see todo.md §1.1.
 */
interface OutboundQueueContract
{
    /**
     * Push task to main queue (considering priority + orderingKey).
     */
    public function push(OutboundTask $task): void;

    /**
     * Pop a ready (scheduled_at <= now) task with visibility lease.
     *
     * Task is hidden from other workers for $visibilityTimeoutSec. On worker crash,
     * the lease expires and the task becomes available again (crash recovery).
     *
     * @param  int  $visibilityTimeoutSec  Time for which the task is removed from the queue.
     * @return OutboundEnvelope|null Envelope with deliveryId set, or null if queue is empty.
     */
    public function pop(int $visibilityTimeoutSec = 60): ?OutboundEnvelope;

    /**
     * Confirm successful processing — remove from in-flight.
     */
    public function ack(OutboundEnvelope $envelope): void;

    /**
     * Return task to queue with delay (for retry).
     *
     * @param  int  $delaySec  Delay before the task becomes available for pop() again.
     */
    public function release(OutboundEnvelope $envelope, int $delaySec): void;

    /**
     * Main queue size: ready + delayed (without in-flight).
     */
    public function size(): int;
}
