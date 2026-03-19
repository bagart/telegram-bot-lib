<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\RateLimit;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;

final class TgBasicRateLimiter implements TgRateLimiterContract
{
    public const string NAME = 'tg-basic';

    public function __construct(
        private readonly ASKCacheWrapper $cache,
    ) {
    }

    public function getRetryDelay(string $key): float
    {
        $retryUntil = (float)($this->cache->get($this->buildKey($key)) ?? 0.0);
        $now = microtime(true);

        return $retryUntil > $now ? $retryUntil - $now : 0.0;
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
        $until = microtime(true) + $seconds;

        // TTL is the blocking duration + buffer to ensure the key exists until it expires
        $this->cache->set($this->buildKey($key), $until, (int)$seconds + 5);
    }

    public function markSent(string $key): void
    {
        // No-op: Reactive limiters don't track usage frequency
    }

    public function reset(string $key): void
    {
        $this->cache->delete($this->buildKey($key));
    }

    private function buildKey(string $key): string
    {
        return sprintf('tg_basic:flood:%s', $key);
    }
}
