<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\Shedding\WatermarkShedPolicy;
use BAGArt\TelegramBot\Outbound\TaskPriority;

describe('WatermarkShedPolicy', function () {
    function shedPolicy(): WatermarkShedPolicy
    {
        return new WatermarkShedPolicy(highWatermarkPct: 90, deferDelaySec: 60);
    }

    it('accepts everything below the high watermark', function (): void {
        $p = shedPolicy();
        $maxSize = 100;

        foreach (TaskPriority::cases() as $priority) {
            expect($p->decide($priority, 89, $maxSize)->isAccept())->toBeTrue();
        }
    });

    it('defers only Low between the watermark and the cap', function (): void {
        $p = shedPolicy();
        $maxSize = 100;

        $decision = $p->decide(TaskPriority::Low, 90, $maxSize);
        expect($decision->isDefer())->toBeTrue()
            ->and($decision->delaySec)->toBe(60);

        expect($p->decide(TaskPriority::Normal, 90, $maxSize)->isAccept())->toBeTrue()
            ->and($p->decide(TaskPriority::High, 95, $maxSize)->isAccept())->toBeTrue()
            ->and($p->decide(TaskPriority::Critical, 99, $maxSize)->isAccept())->toBeTrue();
    });

    it('defers Normal and drops Low at the cap; High/Critical never shed', function (): void {
        $p = shedPolicy();
        $maxSize = 100;

        $low = $p->decide(TaskPriority::Low, 100, $maxSize);
        expect($low->isDrop())->toBeTrue()
            ->and($low->reason)->toBe('load_shed');

        expect($p->decide(TaskPriority::Normal, 150, $maxSize)->isDefer())->toBeTrue()
            ->and($p->decide(TaskPriority::High, 150, $maxSize)->isAccept())->toBeTrue()
            ->and($p->decide(TaskPriority::Critical, 500, $maxSize)->isAccept())->toBeTrue();
    });

    it('always accepts on an unbounded queue', function (): void {
        $p = shedPolicy();

        foreach (TaskPriority::cases() as $priority) {
            expect($p->decide($priority, 999_999, null)->isAccept())->toBeTrue()
                ->and($p->decide($priority, 999_999, 0)->isAccept())->toBeTrue();
        }
    });

    it('rejects out-of-range watermark', function (): void {
        new WatermarkShedPolicy(highWatermarkPct: 0);
    })->throws(InvalidArgumentException::class);
});
