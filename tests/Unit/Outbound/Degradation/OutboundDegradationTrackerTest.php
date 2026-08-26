<?php

declare(strict_types=1);

use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\CircuitBreakerState;
use BAGArt\TelegramBot\Outbound\Degradation\OutboundDegradationState;
use BAGArt\TelegramBot\Outbound\Degradation\OutboundDegradationTracker;

if (! function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../../Helpers.php';
}

describe('OutboundDegradationTracker', function () {
    function tracker(int $failureThreshold = 3, int $recoverStreakSec = 60): OutboundDegradationTracker
    {
        return new OutboundDegradationTracker(
            new KernelCacheAdapter(
                makeCacheWrapper(),
                new InMemoryLocker(),
            ),
            queueFailureThreshold: $failureThreshold,
            recoverStreakSec: $recoverStreakSec,
        );
    }

    it('starts Normal and stays Normal on healthy observations', function (): void {
        $t = tracker();

        expect($t->state())->toBe(OutboundDegradationState::Normal)
            ->and($t->since())->toBeNull()
            ->and($t->observe([CircuitBreakerState::Closed], true, 100))->toBe(OutboundDegradationState::Normal);
    });

    it('goes Degraded immediately when a breaker is open', function (): void {
        $t = tracker();

        expect($t->observe([CircuitBreakerState::Open], true, 100))->toBe(OutboundDegradationState::Degraded)
            ->and($t->state())->toBe(OutboundDegradationState::Degraded)
            ->and($t->since())->toBe(100);
    });

    it('goes Offline after the configured streak of queue failures', function (): void {
        $t = tracker(failureThreshold: 3);

        expect($t->observe([], false, 100))->toBe(OutboundDegradationState::Normal)
            ->and($t->observe([], false, 101))->toBe(OutboundDegradationState::Normal)
            ->and($t->observe([], false, 102))->toBe(OutboundDegradationState::Offline);
    });

    it('recovers stepwise with hysteresis', function (): void {
        $t = tracker(recoverStreakSec: 60);
        $t->observe([], false, 100);
        $t->observe([], false, 101);
        $t->observe([], false, 102); // Offline

        // Healthy again at t=130: within the recovery streak — stays Offline.
        // After the full streak (>= 60s since the last change) recovers directly.
        expect($t->observe([CircuitBreakerState::Closed], true, 130))->toBe(OutboundDegradationState::Offline)
            ->and($t->observe([CircuitBreakerState::Closed], true, 200))->toBe(OutboundDegradationState::Normal);
    });
});
