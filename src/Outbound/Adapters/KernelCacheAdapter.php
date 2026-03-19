<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

use BAGArt\AsyncKernel\Contracts\ASKLockerContract;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundCacheContract;

/**
 * {@see OutboundCacheContract} implementation on top of ASK kernel ({@see ASKCacheWrapper}
 * + {@see ASKLockerContract}).
 *
 * Suitable for non-Redis backends (in-memory, file) and single-process modes.
 *
 * IMPORTANT about {@see incrementWithTtl()}: the ASKCacheWrapper::increment() trait
 * implementation is non-atomic (get+set). Here incrementWithTtl is implemented via a serial
 * section: lock → get → set → unlock. This is safe for single-process / in-memory, but NOT for
 * multi-process Redis. For production (atomic INCR+TTL via Lua) use
 * {@see RedisOutboundCache}. See todo.md §1.3, §6.2.
 *
 * lock/unlock delegate to {@see ASKLockerContract::acquireWithTtl()} /
 * {@see ASKLockerContract::releaseWithOwner()} (Phase 0 methods) — this provides TTL+owner
 * for ordering lock and dedup.
 */
final class KernelCacheAdapter implements OutboundCacheContract
{
    /**
     * Prefix for internal serial-section lock keys (to avoid collision
     * with ordering locks that the caller passes explicitly via lock()/unlock()).
     */
    private const string INCR_LOCK_PREFIX = 'tg_outbound:incr_lock:';

    public function __construct(
        private readonly ASKCacheWrapper $cache,
        private readonly ASKLockerContract $locker,
    ) {
    }

    public function incrementWithTtl(string $key, int $value, int $ttlSec): int
    {
        // Serial section via lock: safe for single-process.
        // For multi-process/Redis — RedisOutboundCache with Lua.
        $lockKey = self::INCR_LOCK_PREFIX.$key;
        $owner = bin2hex(random_bytes(8));

        // acquireWithTtl with a short TTL — in case the caller crashes between lock and unlock.
        if (! $this->locker->acquireWithTtl($lockKey, 5, $owner)) {
            // Concurrent increment should not happen in single-process; if it does —
            // return the current value without incrementing (best-effort, don't block pipeline).
            return (int) $this->cache->get($key, 0);
        }

        try {
            $current = (int) $this->cache->get($key, 0);
            $next = $current + $value;

            // put = setex under the hood (TTL is applied). On each increment — TTL
            // is refreshed (not EXPIRE NX), because the kernel does not support conditional TTL.
            // This is acceptable for metrics: TTL = aggregation window.
            $this->cache->set($key, $next, $ttlSec);

            return $next;
        } finally {
            $this->locker->releaseWithOwner($lockKey, $owner);
        }
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
        return $this->cache->get($key);
    }

    public function put(string $key, mixed $value, int $ttlSec): void
    {
        $this->cache->set($key, $value, $ttlSec);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
