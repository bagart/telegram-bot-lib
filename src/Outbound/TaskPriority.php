<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

/**
 * Outbound task priority.
 *
 * Used as the high-order part of score in Redis sorted set
 * (score = priority.value * 1e10 + createdAt). Pop selects by score DESC,
 * so higher priority goes first; within the same priority — FIFO by createdAt.
 */
enum TaskPriority: int
{
    case Low = 0;
    case Normal = 1;
    case High = 2;
    case Critical = 3;
}
