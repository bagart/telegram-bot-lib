<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

use BAGArt\ASKClientRedis\Lockers\PhpRedisLockerTransport;
use BAGArt\ASKClientRedis\Lockers\RedisLocker;
use BAGArt\ASKClientRedis\Redis\Contract\RedisClientContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundCacheContract;

/**
 * Redis implementation of {@see OutboundCacheContract} with **atomic** incrementWithTtl.
 *
 * For production: metrics and circuit breaker require truly atomic INCR+TTL
 * (multi-process safe). {@see KernelCacheAdapter} uses serial-section
 * (lock+get+set) — acceptable for in-memory/single-process, but not for Redis.
 *
 * - incrementWithTtl: Lua `INCR + EXPIRE NX` (TTL only on key creation, atomic).
 * - lock/unlock: delegates to {@see RedisLocker} (Phase 0: acquireWithTtl/releaseWithOwner).
 * - get/put/forget: direct Redis GET/SETEX/DEL.
 *
 * See todo.md §1.3, §6.2.
 */
final class RedisOutboundCache implements OutboundCacheContract
{
    /**
     * Lua: atomic INCR + EXPIRE NX (TTL only if the key is new).
     * Returns the new counter value.
     *
     * KEYS: [key]
     * ARGV: [value, ttlSec]
     */
    private const string LUA_INCREMENT_WITH_TTL = <<<'LUA'
local key = KEYS[1]
local value = tonumber(ARGV[1])
local ttl = tonumber(ARGV[2])
local current = redis.call("INCRBY", key, value)
-- EXPIRE NX: TTL only if the key is new (current == value, i.e. just created).
-- Alternative: PEXPIRE key ttl NX — but INCRBY does not indicate "key existed".
-- So we set TTL only on the first increment (current == value).
if current == value then
    redis.call("EXPIRE", key, ttl)
end
return current
LUA;

    private readonly RedisLocker $locker;

    public function __construct(
        private readonly RedisClientContract $redis,
    ) {
        $this->locker = new RedisLocker(PhpRedisLockerTransport::fromClient($redis));
    }

    public function incrementWithTtl(string $key, int $value, int $ttlSec): int
    {
        $result = $this->redis->eval(
            self::LUA_INCREMENT_WITH_TTL,
            [$key, (string) $value, (string) $ttlSec],
            1,
        );

        return (int) $result;
    }

    public function lock(string $key, int $ttlSec, ?string $owner = null): bool
    {
        return $this->locker->acquireWithTtl($key, $ttlSec, $owner);
    }

    public function unlock(string $key, ?string $owner = null): void
    {
        $this->locker->releaseWithOwner($key, $owner);
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);

        return $value === false ? null : $value;
    }

    public function put(string $key, mixed $value, int $ttlSec): void
    {
        // SETEX with TTL. Value — scalar (string/int); for arrays the caller serializes.
        $this->redis->setex($key, max(1, $ttlSec), (string) $value);
    }

    public function forget(string $key): void
    {
        $this->redis->del($key);
    }
}
