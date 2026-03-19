<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\OutboundEnvelope;

/**
 * Capability: queue with visibility lease renewal.
 *
 * Implemented by: Redis, in-memory, SQS (ChangeMessageVisibility), Beanstalkd (touch).
 * NOT implemented by: RabbitMQ (relies on visibility timeout + at-least-once).
 *
 * Worker via LeaseRenewer extends lease every 30s for long sends.
 * If capability is unavailable — renewal is skipped (no-op).
 *
 * @see todo.md §1.2, §4.2.
 */
interface LeaseRenewableQueueContract
{
    /**
     * Extend task lease by $seconds.
     *
     * @return bool true — lease renewed; false — lease lost (another worker already took the task).
     */
    public function renewLease(OutboundEnvelope $envelope, int $seconds): bool;
}
