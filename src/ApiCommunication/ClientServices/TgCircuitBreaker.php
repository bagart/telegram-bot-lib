<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;

class TgCircuitBreaker implements TgCircuitBreakerContract
{
    private const FAILURE_THRESHOLD = 5;

    private const RECOVERY_TIMEOUT = 30;

    public function __construct(
        private ASKCacheWrapper $cache,
    ) {
    }

    public function canExecute(string $method): bool
    {
        if (in_array($method, ['getUpdates', 'getMe'], true)) {
            return true;
        }

        $failureCount = (int)$this->cache->get("tg_circuit_{$method}_failures", 0);

        if ($failureCount >= self::FAILURE_THRESHOLD) {
            $openedAt = (int)$this->cache->get("tg_circuit_{$method}_opened_at", 0);
            if (!($openedAt > 0 && (time() - $openedAt) >= self::RECOVERY_TIMEOUT)) {
                return false;
            }

            $this->recordSuccess($method);
        }

        return true;
    }

    public function recordSuccess(string $method): void
    {
        $this->cache->forget("tg_circuit_{$method}_failures");
        $this->cache->forget("tg_circuit_{$method}_opened_at");
    }

    public function recordFailure(string $method, \Throwable $exception): void
    {
        if (
            $exception instanceof TgApiRateLimitException
            || $exception instanceof TgApiNetworkException
        ) {
            return;
        }

        $this->cache->increment("tg_circuit_{$method}_failures");
        $this->cache->touch("tg_circuit_{$method}_failures", self::RECOVERY_TIMEOUT);
        $this->cache->put("tg_circuit_{$method}_opened_at", time(), self::RECOVERY_TIMEOUT);
    }

    public function reset(): void
    {
        $this->cache->flush();
    }
}
