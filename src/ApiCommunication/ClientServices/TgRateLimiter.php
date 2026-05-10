<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;

/**
 * Notes:
 * Telegram long polling should rely on:
 * timeout=30..50 sec
 * instead of high-frequency request spam.
 */
final class TgRateLimiter implements TgRateLimiterContract
{
    private const DEFAULT_WINDOW_SECONDS = 60;
    private const DEFAULT_MAX_REQUESTS = 120;

    /**
     * Minimum delay between getUpdates calls.
     *
     * With timeout=30 long polling this is very safe.
     */
    private const GET_UPDATES_MIN_INTERVAL_SECONDS = 2;

    public function __construct(
        private readonly ?TgBotCacheWrapper $cache = null,
    ) {
    }

    public function acquire(string $key, int $tokens = 1): bool
    {
        $cache = $this->cache ?? TgBotCacheWrapper::build();

        if ($this->isGetUpdates($key)) {
            return $this->acquirePollingLock($cache, $key);
        }

        $cacheKey = 'tg_rate_limit_' . $key;

        $current = (int) $cache->get($cacheKey, 0);

        if (($current + $tokens) > self::DEFAULT_MAX_REQUESTS) {
            return false;
        }

        $cache->put(
            $cacheKey,
            $current + $tokens,
            self::DEFAULT_WINDOW_SECONDS
        );

        return true;
    }

    public function available(string $key): int
    {
        $cache = $this->cache ?? TgBotCacheWrapper::build();

        if ($this->isGetUpdates($key)) {
            return 1;
        }

        $cacheKey = 'tg_rate_limit_' . $key;
        $current = (int) $cache->get($cacheKey, 0);

        return max(
            0,
            self::DEFAULT_MAX_REQUESTS - $current
        );
    }

    public function reset(string $key): void
    {
        $cache = $this->cache ?? TgBotCacheWrapper::build();

        $cache->forget('tg_rate_limit_' . $key);
    }

    private function acquirePollingLock(
        TgBotCacheWrapper $cache,
        string $key,
    ): bool {
        $cacheKey = 'tg_polling_lock_' . $key;

        $lastRequestAt = (float) $cache->get($cacheKey, 0.0);
        $now = microtime(true);

        if (
            ($now - $lastRequestAt)
            < self::GET_UPDATES_MIN_INTERVAL_SECONDS
        ) {
            return false;
        }

        $cache->put(
            $cacheKey,
            $now,
            120
        );

        return true;
    }

    private function isGetUpdates(string $key): bool
    {
        return str_contains($key, 'getUpdates');
    }
}
