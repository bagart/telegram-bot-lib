<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Ordering;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundOrderingQueueContract;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\TgSender;

/**
 * Strategy for determining the task's ordering key.
 *
 * Returns a strict ordering key (per-key FIFO) or null — task without
 * ordering (broadcast, sent in parallel).
 *
 * Ordering is enforced by the queue implementation ({@see OutboundOrderingQueueContract}),
 * not by middleware.
 */
interface OrderingStrategyContract
{
    /**
     * @param  OutboundTask  $task  Task.
     * @return string|null Ordering key (e.g. 'chat_id:session_id'), or null = broadcast.
     */
    public function keyFor(OutboundTask $task): ?string;

    /**
     * Determine ordering key from raw DTO data before task creation.
     *
     * Used in {@see TgSender} — accepts
     * serialized DTO data (array, toArray result) and determines the key
     * without needing to construct an OutboundTask.
     *
     * @param  array<string,mixed>  $dtoData  Raw DTO data (chat_id, text, …).
     * @return string|null Ordering key, or null = broadcast.
     */
    public function keyForDto(array $dtoData): ?string;
}
