<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;

/**
 * Capability: DLQ with atomic entry extraction.
 *
 * Implemented by: Redis (Lua HGET+HDEL), DB (SELECT FOR UPDATE), in-memory.
 * For SQS — receive+delete+dedup; RabbitMQ — get+ack.
 *
 * Without this capability Worker uses fallback (distributed lock + best-effort),
 * and CLI DLQ commands are unavailable. No BadMethodCallException at runtime —
 * only explicit instanceof checks.
 *
 * @see todo.md §1.2, §5.6.
 */
interface AtomicDlqQueueContract
{
    /**
     * Atomically fetch and remove entry from DLQ (for --retry).
     *
     * @param  string  $channel  DLQ channel name (e.g. 'tg-dlq:bot1').
     * @param  string  $entryId  Entry identifier (= task.id).
     * @return string|null JSON DeadLetterEntry or null if entry was already removed.
     */
    public function atomicFetchAndRemoveFromDlq(string $channel, string $entryId): ?string;

    /**
     * Push task to DLQ.
     *
     * @return string Created entry ID (entryId).
     */
    public function pushToDeadLetter(OutboundEnvelope $envelope, string $reason): string;

    /**
     * View DLQ entries without extraction.
     *
     * @param  string|null  $channel  Channel name; null — across all channels (if supported).
     * @param  int  $limit  Maximum entries.
     * @return DeadLetterEntry[]|array<int, array{id:string,channel:string,entry:string}> DLQ entries.
     */
    public function listDeadLetter(?string $channel, int $limit = 50): array;

    /**
     * DLQ size (number of entries).
     *
     * @param  string|null  $channel  Channel; null — total across all channels.
     */
    public function deadLetterSize(?string $channel = null): int;
}
