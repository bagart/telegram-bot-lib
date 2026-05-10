<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Wrappers;

use BAGArt\TelegramBot\Exceptions\TgBotConfigurationException;
use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Store;
use Psr\SimpleCache\CacheInterface;

final class TgBotCacheWrapper implements CacheInterface, Store
{
    public static CacheInterface|Store|null $initCache = null;

    public static function build(): self
    {
        if (self::$initCache === null) {
            throw new TgBotConfigurationException('TgBotCacheWrapper::build() called without initCache. Call TgBotCacheWrapper::init() first.');
        }
        return new self(self::$initCache);
    }

    public function __construct(
        private readonly CacheInterface|Store $cache,
    ) {
        self::$initCache ??= $cache;
    }

    public static function init(CacheInterface|Store $cache): void
    {
        self::$initCache = $cache;
    }

    public function get($key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->cache->getMultiple($keys, $default);
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return $this->cache->setMultiple($values, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    public function pull(array|string $key, mixed $default = null): mixed
    {
        return $this->cache->pull($key, $default);
    }

    public function put($key, mixed $value, $ttl): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function add(string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null): bool
    {
        return $this->cache->add($key, $value, $ttl);
    }

    public function increment($key, mixed $value = 1): int|bool
    {
        return $this->cache->increment($key, $value);
    }

    public function decrement($key, mixed $value = 1): int|bool
    {
        return $this->cache->decrement($key, $value);
    }

    public function forever($key, mixed $value): bool
    {
        return $this->cache->forever($key, $value);
    }

    public function remember(
        string $key,
        DateTimeInterface|DateInterval|Closure|int|null $ttl,
        Closure $callback,
    ): mixed {
        return $this->cache->remember($key, $ttl, $callback);
    }

    public function sear(string $key, Closure $callback): mixed
    {
        return $this->cache->sear($key, $callback);
    }

    public function rememberForever(string $key, Closure $callback): mixed
    {
        return $this->cache->rememberForever($key, $callback);
    }

    public function forget($key): bool
    {
        return $this->cache->delete($key);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function getStore(): Store
    {
        return $this->cache->getStore();
    }

    public function many(array $keys)
    {
        return $this->cache->many($keys);
    }

    public function putMany(array $values, $seconds)
    {
        return $this->cache->putMany($values, $seconds);
    }

    public function flush()
    {
        return $this->cache->flush();
    }

    public function getPrefix()
    {
        return $this->cache->getPrefix();
    }

    public function touch($key, $seconds): bool
    {
        if (method_exists($this->cache, 'touch')) {
            return $this->cache->touch($key, $seconds);
        }

        return $this->cache->set(
            $key,
            $this->cache->get($key, $seconds) ?? null,
            $seconds
        );
    }
}
