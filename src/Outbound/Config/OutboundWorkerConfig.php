<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Config;

final class OutboundWorkerConfig
{
    public function __construct(
        public readonly int $visibilityTimeoutSec = 60,
        public readonly int $maxAttempts = 5,
        public readonly int $maxAgeSec = 3600,
        public readonly int $minAttemptsForExpiry = 2,
        public readonly bool $useLuaOptimization = true,
        public readonly int $renewalIntervalSec = 30,
        public readonly int $maxRenewals = 3,
        public readonly int $cbFailureThreshold = 5,
        public readonly int $cbOpenTimeoutSec = 60,
        public readonly int $metricsRetentionHours = 168,
        public readonly int $defaultRetryDelaySec = 3,
        public readonly int $maxConcurrentFibers = 500,
        public readonly int $maxDlqRedeliveries = 3,
    ) {
    }
}
