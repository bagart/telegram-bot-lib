<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\Fairness\AgingFairnessPolicy;
use BAGArt\TelegramBot\Outbound\TaskPriority;

describe('AgingFairnessPolicy', function () {
    function agingPolicy(int $laneCrossSec = 100, int $maxAgeSec = 1000): AgingFairnessPolicy
    {
        return new AgingFairnessPolicy(laneCrossSec: $laneCrossSec, maxAgeSec: $maxAgeSec);
    }

    it('keeps fresh tasks in strict lane order', function (): void {
        $p = agingPolicy();
        $now = 5000;
        $fresh = new DateTimeImmutable('@'.$now);

        $scores = [
            TaskPriority::Low->value => $p->score(TaskPriority::Low, $fresh, $now),
            TaskPriority::Normal->value => $p->score(TaskPriority::Normal, $fresh, $now),
            TaskPriority::High->value => $p->score(TaskPriority::High, $fresh, $now),
            TaskPriority::Critical->value => $p->score(TaskPriority::Critical, $fresh, $now),
        ];

        expect($scores)->toBe([
            TaskPriority::Low->value => 0.0,
            TaskPriority::Normal->value => 1e10,
            TaskPriority::High->value => 2e10,
            TaskPriority::Critical->value => 3e10,
        ]);
    });

    it('grows monotonically with waiting age', function (): void {
        $p = agingPolicy();
        $createdAt = new DateTimeImmutable('@1000');

        $s0 = $p->score(TaskPriority::Low, $createdAt, 1000);
        $s50 = $p->score(TaskPriority::Low, $createdAt, 1050);
        $s100 = $p->score(TaskPriority::Low, $createdAt, 1100);

        expect($s50)->toBeGreaterThan($s0)
            ->and($s100)->toBeGreaterThan($s50);
    });

    it('crosses one lane after laneCrossSec of waiting (anti-starvation)', function (): void {
        $p = agingPolicy(laneCrossSec: 100, maxAgeSec: 10_000);
        $lowOld = new DateTimeImmutable('@1000');

        // Low waited 150s: boost = 1.5 lanes → beats a fresh Normal.
        $agedLow = $p->score(TaskPriority::Low, $lowOld, 1150);
        $freshNormal = $p->score(TaskPriority::Normal, new DateTimeImmutable('@1150'), 1150);

        expect($agedLow)->toBeGreaterThan($freshNormal);
    });

    it('preserves FIFO within the same lane (older wins)', function (): void {
        $p = agingPolicy();
        $older = new DateTimeImmutable('@1000');
        $newer = new DateTimeImmutable('@1040');
        $now = 1100;

        expect($p->score(TaskPriority::Normal, $older, $now))
            ->toBeGreaterThan($p->score(TaskPriority::Normal, $newer, $now));
    });

    it('stops accruing age past maxAgeSec so scores stay bounded', function (): void {
        $p = agingPolicy(maxAgeSec: 50);
        $createdAt = new DateTimeImmutable('@0');

        $atCap = $p->scoreByAge(TaskPriority::Low, 50);
        $beyond = $p->scoreByAge(TaskPriority::Low, 999_999);

        expect($beyond)->toBe($atCap)
            ->and($beyond)->toBeLessThan(1e10);
    });

    it('laneFloor returns the base of the requested lane', function (): void {
        $p = agingPolicy();

        expect($p->laneFloor(TaskPriority::Low))->toBe(0.0)
            ->and($p->laneFloor(TaskPriority::Critical))->toBe(3e10);
    });

    it('rejects non-positive laneCrossSec', function (): void {
        new AgingFairnessPolicy(laneCrossSec: 0);
    })->throws(InvalidArgumentException::class);
});
