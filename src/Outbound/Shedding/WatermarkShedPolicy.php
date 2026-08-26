<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Shedding;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundShedPolicyContract;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * Deterministic watermark bands:
 *
 * | occupancy            | Low          | Normal       | High   | Critical |
 * | < highWatermarkPct   | Accept       | Accept       | Accept | Accept   |
 * | >= high, < maxSize   | Defer(60s)   | Accept       | Accept | Accept   |
 * | >= maxSize           | Drop         | Defer(60s)   | Accept | Accept   |
 *
 * Unbounded queue (maxSize null/0) always accepts. High/Critical are never
 * shed; drops hit only Low and go through the DLQ with reason `load_shed`.
 */
final class WatermarkShedPolicy implements OutboundShedPolicyContract
{
    public function __construct(
        private readonly int $highWatermarkPct = 90,
        private readonly int $deferDelaySec = 60,
    ) {
        if ($highWatermarkPct < 1 || $highWatermarkPct > 100) {
            throw new \InvalidArgumentException('highWatermarkPct must be within 1..100');
        }
    }

    public function decide(TaskPriority $priority, int $size, ?int $maxSize): ShedDecision
    {
        if ($maxSize === null || $maxSize < 1) {
            return ShedDecision::Accept();
        }

        $occupancyPct = (int) floor($size / $maxSize * 100);

        if ($priority === TaskPriority::Critical || $priority === TaskPriority::High) {
            return ShedDecision::Accept();
        }

        if ($size >= $maxSize) {
            return $priority === TaskPriority::Low
                ? ShedDecision::Drop('load_shed')
                : ShedDecision::Defer($this->deferDelaySec);
        }

        if ($occupancyPct >= $this->highWatermarkPct && $priority === TaskPriority::Low) {
            return ShedDecision::Defer($this->deferDelaySec);
        }

        return ShedDecision::Accept();
    }
}
