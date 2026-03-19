<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\RateLimit;

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Exceptions\TgBotConfigurationException;

final class TgAdvancedRateLimiter implements TgRateLimiterContract
{
    public const string NAME = 'tg-advanced';

    public function __construct(
        private readonly ASKCacheWrapper $cache,
        private readonly float $safetyMargin = 0.1,
    ) {
        if ($safetyMargin >= 1 || $safetyMargin < 0) {
            throw new TgBotConfigurationException('safetyMargin must be between 0 and 0.999...');
        }
    }

    public function getRetryDelay(string $key): float
    {
        $now = microtime(true);
        $parts = explode(':', $key, 3);
        $botId = $parts[0] ?? 'default';
        $chatId = $parts[2] ?? null;

        $keys = [
            'flood_key'    => "tg_limit:{$botId}:flood:{$key}",
            'flood_global' => "tg_limit:{$botId}:flood:global",
            'global_last'  => "tg_limit:{$botId}:global:last",
        ];

        if ($chatId !== null) {
            $keys['chat_last'] = "tg_limit:{$botId}:chat:{$chatId}";
        }

        /**
         * Performance note: getMultiple() uses MGET under the hood in Redis.
         * This can be further optimized by using a raw Redis Lua script to
         * reduce network RTT to exactly one round-trip.
         */
        $values = $this->cache->getMultiple(array_values($keys));
        $data = array_combine(array_keys($keys), is_array($values) ? $values : iterator_to_array($values));

        $retryAfter = min((float)($data['flood_key'] ?? 0.0), (float)($data['flood_global'] ?? 0.0));
        if ($retryAfter > $now) {
            return $retryAfter - $now;
        }

        if (str_contains($key, 'getUpdates')) {
            return 0.0;
        }

        $minGlobalInterval = 1.0 / (30.0 * (1.0 - $this->safetyMargin));
        $delay = max(0.0, ((float)($data['global_last'] ?? 0.0) + $minGlobalInterval) - $now);

        if ($chatId !== null && isset($data['chat_last'])) {
            $isPrivate = (int)$chatId > 0;
            $minChatInterval = $isPrivate
                ? 1.0 / (1.0 - $this->safetyMargin)
                : 60.0 / (20.0 * (1.0 - $this->safetyMargin));

            $delay = max($delay, ((float)$data['chat_last'] + $minChatInterval) - $now);
        }

        return max(0.0, $delay);
    }

    public function markSent(string $key): void
    {
        $now = microtime(true);
        $parts = explode(':', $key, 3);
        $botId = $parts[0] ?? 'default';
        $chatId = $parts[2] ?? null;

        $data = ["tg_limit:{$botId}:global:last" => $now];
        if ($chatId !== null) {
            $data["tg_limit:{$botId}:chat:{$chatId}"] = $now;
        }

        /**
         * Performance note: setMultiple() maps to MSET in Redis.
         * For atomic increments and stricter rate limiting, consider using
         * a custom Redis Lua script (EVAL).
         */
        $this->cache->setMultiple($data, 60);
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
        $until = microtime(true) + ($seconds * (1.0 + $this->safetyMargin));
        $parts = explode(':', $key, 3);
        $botId = $parts[0] ?? 'default';
        $ttl = (int)$seconds + 60;

        $this->cache->setMultiple([
            "tg_limit:{$botId}:flood:{$key}" => $until,
            "tg_limit:{$botId}:flood:global" => $until,
        ], $ttl);
    }

    public function reset(string $key): void
    {
        $parts = explode(':', $key, 3);
        $botId = $parts[0] ?? 'default';
        $chatId = $parts[2] ?? null;

        $keys = ["tg_limit:{$botId}:flood:{$key}"];
        if ($chatId !== null) {
            $keys[] = "tg_limit:{$botId}:chat:{$chatId}";
        }

        $this->cache->deleteMultiple($keys);
    }
}
