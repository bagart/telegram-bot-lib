<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

/**
 * Capability: purge expired entries by timestamp.
 *
 * Implemented by: Redis (ZREMRANGEBYSCORE / SCAN+HDEL), DB (DELETE WHERE), in-memory (filter).
 *
 * Used by DLQ auto-purge and CLI --purge.
 *
 * @see todo.md §1.2.
 */
interface PurgeableQueueContract
{
    /**
     * Remove entries older than $beforeTimestamp from channel(s).
     *
     * @param  string  $channelPattern  Channel pattern (e.g. 'tg-dlq:*').
     * @param  int  $beforeTimestamp  Unix timestamp; entries with failedAt < this are deleted.
     * @return int Number of deleted entries.
     */
    public function purgeExpired(string $channelPattern, int $beforeTimestamp): int;
}
