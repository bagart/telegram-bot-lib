<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;

class TgRateLimiter implements TgRateLimiterContract
{
    private const WINDOW_SECONDS = 60;

    private const MAX_REQUESTS = 50;

    public function __construct(private TgBotCacheWrapper $cache)
    {
    }

    public function acquire(string $key, int $tokens = 1): bool
    {
        $cacheKey = "tg_rate_limit_{$key}";
        $current = (int)$this->cache->get($cacheKey, 0);

        if ($current + $tokens <= self::MAX_REQUESTS) {
            $this->cache->increment($cacheKey, $tokens);
            $this->cache->put($cacheKey, $current + $tokens, self::WINDOW_SECONDS);

            return true;
        }

        return false;
    }

    public function available(string $key): int
    {
        $cacheKey = "tg_rate_limit_{$key}";
        $current = (int)$this->cache->get($cacheKey, 0);

        return max(0, self::MAX_REQUESTS - $current);
    }

    public function reset(string $key): void
    {
        $cacheKey = "tg_rate_limit_{$key}";
        $this->cache->forget($cacheKey);
    }
}
