<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

/**
 * Outbound cache contract: atomic INCR+TTL and lock with owner.
 *
 * Used for metrics, circuit breaker and ordering lock.
 *
 * IMPORTANT: {@see incrementWithTtl()} — atomic operation (Redis Lua `INCR + EXPIRE NX`).
 * The core ASKCacheContract::increment() is not atomic in trait implementation (get+set),
 * therefore cannot be used directly — only through this contract.
 * TTL is set only on key creation (EXPIRE NX), not reset.
 *
 * @see todo.md §1.3.
 */
interface OutboundCacheContract
{
    /**
     * Atomic increment with TTL on key creation.
     *
     * @param  string  $key  Key.
     * @param  int  $value  Increment value (usually 1).
     * @param  int  $ttlSec  TTL in seconds — applied only if key is new.
     * @return int New counter value.
     */
    public function incrementWithTtl(string $key, int $value, int $ttlSec): int;

    /**
     * Acquire lock with TTL and owner (for ordering + dedup).
     *
     * @param  string  $key  Lock key.
     * @param  int  $ttlSec  Lock TTL in seconds (auto-release on crash).
     * @param  string|null  $owner  Owner identifier (for safe-release); null — no owner.
     * @return bool true — lock acquired; false — busy.
     */
    public function lock(string $key, int $ttlSec, ?string $owner = null): bool;

    /**
     * Release lock. If $owner is specified — releases only if owner matches.
     *
     * @param  string|null  $owner  Lock owner; null — unconditional release.
     */
    public function unlock(string $key, ?string $owner = null): void;

    /**
     * @return mixed Value or null if key is missing.
     */
    public function get(string $key): mixed;

    /**
     * Write value with TTL.
     *
     * @param  mixed  $value  Value (must be serializable by the adapter).
     */
    public function put(string $key, mixed $value, int $ttlSec): void;

    /**
     * Delete key (no-op if missing).
     */
    public function forget(string $key): void;
}
