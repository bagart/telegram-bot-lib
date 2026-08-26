<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\TaskPriority;
use DateTimeImmutable;

/**
 * Anti-starvation scoring for outbound lanes (06 §45–§46).
 *
 * Turns (base priority, creation time) into a sorted-set score. Fresh tasks
 * keep strict lane order; the longer a task waits, the higher its effective
 * score climbs, so a lower lane eventually overtakes fresher higher lanes
 * instead of starving forever.
 */
interface OutboundFairnessPolicyContract
{
    /**
     * Sorted-set score: monotonic in base priority and in waiting age.
     *
     * @param  TaskPriority  $base  Static lane of the task.
     * @param  DateTimeImmutable  $createdAt  When the task entered the queue.
     * @param  int  $now  Current Unix timestamp.
     */
    public function score(
        TaskPriority $base,
        DateTimeImmutable $createdAt,
        int $now,
    ): float;
}
