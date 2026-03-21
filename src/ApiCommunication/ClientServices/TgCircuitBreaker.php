<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;

class TgCircuitBreaker implements TgCircuitBreakerContract
{
    private const FAILURE_THRESHOLD = 5;

    private const RECOVERY_TIMEOUT = 30;

    public function __construct(private TgBotCacheWrapper $cache)
    {
    }

    public function canExecute(string $method): bool
    {
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

    public function recordFailure(string $method): void
    {
        $failureCount = (int)$this->cache->get("tg_circuit_{$method}_failures", 0);
        $failureCount++;

        $this->cache->put("tg_circuit_{$method}_failures", $failureCount, self::RECOVERY_TIMEOUT);
        $this->cache->put("tg_circuit_{$method}_opened_at", time(), self::RECOVERY_TIMEOUT);
    }

    public function reset(): void
    {
        $this->cache->flush();
    }
}
