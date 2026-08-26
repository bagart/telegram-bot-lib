<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Fairness;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundFairnessPolicyContract;
use BAGArt\TelegramBot\Outbound\TaskPriority;
use DateTimeImmutable;

/**
 * Aging fairness: score = base * laneWidth + min(age, maxAgeSec) * boostPerSec.
 *
 * boostPerSec = laneWidth / laneCrossSec, i.e. a continuously waiting task
 * crosses one lane every $laneCrossSec (default 900s): Low overtakes fresh
 * Normal after 15 minutes, fresh High after 30, fresh Critical after 45.
 * Within the same base, older tasks score higher — real FIFO for the
 * broadcast lane. Age stops accruing past $maxAgeSec so scores stay bounded.
 */
final class AgingFairnessPolicy implements OutboundFairnessPolicyContract
{
    private const float LANE_WIDTH = 1e10;

    public function __construct(
        private readonly int $laneCrossSec = 900,
        private readonly int $maxAgeSec = 3600,
    ) {
        if ($laneCrossSec < 1) {
            throw new \InvalidArgumentException('laneCrossSec must be >= 1');
        }
    }

    public function score(TaskPriority $base, DateTimeImmutable $createdAt, int $now): float
    {
        $age = max(0, $now - $createdAt->getTimestamp());

        return $base->value * self::LANE_WIDTH + min($age, $this->maxAgeSec) * $this->boostPerSec();
    }

    /**
     * Score a task would have if it had waited $age seconds — for Lua-side
     * recomputation and tests.
     */
    public function scoreByAge(TaskPriority $base, int $ageSec): float
    {
        return $base->value * self::LANE_WIDTH + min(max(0, $ageSec), $this->maxAgeSec) * $this->boostPerSec();
    }

    /**
     * Lowest score inside the lane of $base: pop-floor filtering keeps only
     * members scoring >= this value when lanes below $base are paused.
     */
    public function laneFloor(TaskPriority $base): float
    {
        return $base->value * self::LANE_WIDTH;
    }

    private function boostPerSec(): float
    {
        return self::LANE_WIDTH / $this->laneCrossSec;
    }

    /** Score gain per waiting second — mirrors boostPerSec() for Lua callers. */
    public function boostPerSecond(): float
    {
        return $this->boostPerSec();
    }

    public function maxAgeSeconds(): int
    {
        return $this->maxAgeSec;
    }
}
