<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

/**
 * Capability: discover DLQ channels by pattern.
 *
 * Implemented by: Redis (SCAN), in-memory (filter), Beanstalkd (list_tubes).
 * For SQS/RabbitMQ — via external registry.
 *
 * @see todo.md §1.2.
 */
interface ChannelDiscoverableQueueContract
{
    /**
     * @param  string  $pattern  Channel name pattern (e.g. 'tg-dlq:*').
     * @return string[] DLQ channel names (e.g. ['tg-dlq:bot1', 'tg-dlq:bot2']).
     */
    public function getDlqChannels(string $pattern): array;
}
