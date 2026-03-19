<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

/**
 * Marker-job for passing opaque payload through Laravel Queue.
 *
 * Laravel serializes the job object into a JSON envelope {data: {command: serialized}}.
 * This class stores the raw payload (JSON OutboundEnvelope) as a public string —
 * LaravelQueueAdapter::pop() extracts it via unserialize(data.command)->payload.
 *
 * Pattern mirrors ASK QueueLaravelJob (php-async-kernel-client).
 */
final class OutboundLaravelJob
{
    public function __construct(
        public readonly string $payload,
    ) {
    }
}
