<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\Shedding\ShedDecision;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * Push-side load shedding policy (06 §44): decides what happens to an
 * incoming task given current queue occupancy. Critical work must never be
 * shed; dropping is allowed only for non-critical lanes and must be
 * observable (DLQ, counters).
 */
interface OutboundShedPolicyContract
{
    /**
     * @param  int  $size  Current queue occupancy (ready + delayed + inflight).
     * @param  int|null  $maxSize  Queue capacity; null/0 = unbounded.
     */
    public function decide(
        TaskPriority $priority,
        int $size,
        ?int $maxSize,
    ): ShedDecision;
}
