<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Degradation;

/**
 * Aggregate degradation state of the outbound subsystem (06 §43):
 * predefined transitions instead of unbounded retry spawning.
 */
enum OutboundDegradationState: string
{
    case Normal = 'normal';

    /** Circuit breaker open or shedding active — sending continues with backoff. */
    case Degraded = 'degraded';

    /** Queue driver unavailable — outbound work cannot make progress. */
    case Offline = 'offline';

    public function isWorseThan(self $other): bool
    {
        return $this->rank() > $other->rank();
    }

    private function rank(): int
    {
        return match ($this) {
            self::Normal => 0,
            self::Degraded => 1,
            self::Offline => 2,
        };
    }
}
