<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

/**
 * Outbound task priority.
 *
 * Static lane of the task. Queue scores are computed by the fairness policy
 * ({@see OutboundFairnessPolicyContract}; default: aging — a waiting task's
 * score grows until it crosses into fresher higher lanes, preventing
 * starvation). Pop selects by score DESC.
 */
enum TaskPriority: int
{
    case Low = 0;
    case Normal = 1;
    case High = 2;
    case Critical = 3;
}
