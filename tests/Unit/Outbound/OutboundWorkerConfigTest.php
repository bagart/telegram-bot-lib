<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;

describe('OutboundWorkerConfig', function () {
    it('constructs with sensible defaults', function () {
        $config = new OutboundWorkerConfig();

        expect($config->visibilityTimeoutSec)->toBe(60)
            ->and($config->maxAttempts)->toBe(5)
            ->and($config->maxAgeSec)->toBe(3600)
            ->and($config->minAttemptsForExpiry)->toBe(2)
            ->and($config->useLuaOptimization)->toBeTrue()
            ->and($config->renewalIntervalSec)->toBe(30)
            ->and($config->maxRenewals)->toBe(3)
            ->and($config->cbFailureThreshold)->toBe(5)
            ->and($config->cbOpenTimeoutSec)->toBe(60)
            ->and($config->metricsRetentionHours)->toBe(168)
            ->and($config->defaultRetryDelaySec)->toBe(3)
            ->and($config->maxConcurrentFibers)->toBe(500);
    });

    it('allows overriding specific values', function () {
        $config = new OutboundWorkerConfig(
            visibilityTimeoutSec: 120,
            maxAttempts: 10,
            maxConcurrentFibers: 100,
        );

        expect($config->visibilityTimeoutSec)->toBe(120)
            ->and($config->maxAttempts)->toBe(10)
            ->and($config->maxConcurrentFibers)->toBe(100)
            ->and($config->defaultRetryDelaySec)->toBe(3); // default untouched
    });
});
