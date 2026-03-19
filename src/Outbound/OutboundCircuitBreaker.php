<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundCacheContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundCircuitBreakerContract;

/**
 * Atomic per-bot circuit breaker (todo_task.md §5.11, todo.md §6.2).
 *
 * Lifecycle:
 *   Closed  → N consecutive failures → Open
 *   Open    → backoff expiry → HalfOpen (one probe task)
 *   HalfOpen → success → Closed; failure → Open (with backoff escalation)
 *
 * Exponential backoff: 1s → 5s → 30s → 300s (5min, ceiling).
 * Each new open increments the backoff stage; success resets to 0.
 *
 * Atomicity: failure counter via {@see OutboundCacheContract::incrementWithTtl()}
 * (Lua INCR+EXPIRE NX), NOT via get+put (race condition). All operations are stateless —
 * state is entirely in cache, breaker can be reconstructed in any process.
 *
 * Cache keys (TTL = max backoff, so stale keys don't accumulate):
 *   tg_outbound:cb_state:{botId}     — closed | open | half-open
 *   tg_outbound:cb_failures:{botId}  — consecutive failure counter
 *   tg_outbound:cb_backoff:{botId}   — backoff stage number (0,1,2,…)
 */
final class OutboundCircuitBreaker implements OutboundCircuitBreakerContract
{
    private const string STATE_KEY_PREFIX = 'tg_outbound:cb_state:';

    private const string FAILURES_KEY_PREFIX = 'tg_outbound:cb_failures:';

    private const string BACKOFF_KEY_PREFIX = 'tg_outbound:cb_backoff:';

    /** Exponential backoff steps (seconds): 1s → 5s → 30s → 300s (ceiling). */
    private const array BACKOFF_STEPS = [1, 5, 30, 300];

    private const int STATE_TTL_SEC = 600;

    public function __construct(
        private readonly OutboundCacheContract $cache,
        private readonly int $failureThreshold = 5,
        private readonly int $openTimeoutSec = 60,
    ) {
    }

    public function allowsRequest(string $botId): bool
    {
        return match ($this->getRawState($botId)) {
            'open' => false,
            'half-open' => true,
            default => true,
        };
    }

    public function recordFailure(string $botId): void
    {
        $state = $this->getRawState($botId);

        // In half-open, a single failure re-opens CB (with backoff escalation).
        if ($state === 'half-open') {
            $this->tripOpen($botId);

            return;
        }

        $failKey = self::FAILURES_KEY_PREFIX.$botId;
        $count = $this->cache->incrementWithTtl($failKey, 1, self::STATE_TTL_SEC);

        if ($count >= $this->failureThreshold) {
            $this->tripOpen($botId);
        }
    }

    public function recordSuccess(string $botId): void
    {
        // Success fully resets the breaker: closed state, counters and backoff to 0.
        $this->cache->put(self::STATE_KEY_PREFIX.$botId, 'closed', self::STATE_TTL_SEC);
        $this->cache->forget(self::FAILURES_KEY_PREFIX.$botId);
        $this->cache->forget(self::BACKOFF_KEY_PREFIX.$botId);
    }

    public function getState(string $botId): CircuitBreakerState
    {
        return CircuitBreakerState::from($this->getRawState($botId) ?? 'closed');
    }

    /**
     * Transition breaker to Open: persist state, set backoff-TTL,
     * after which allowsRequest will see half-open.
     */
    private function tripOpen(string $botId): void
    {
        $stage = $this->cache->incrementWithTtl(self::BACKOFF_KEY_PREFIX.$botId, 1, self::STATE_TTL_SEC);
        $delay = $this->backoffForStage($stage);

        // Set state=open with TTL=delay: once the key expires, getRawState returns
        // half-open (open for one probe). Backoff stage is kept in a separate key.
        $this->cache->put(self::STATE_KEY_PREFIX.$botId, 'open', $delay);
        $this->cache->forget(self::FAILURES_KEY_PREFIX.$botId);
    }

    /**
     * Backoff delay for stage (1-indexed): 1→1s, 2→5s, 3→30s, ≥4→300s.
     */
    private function backoffForStage(int $stage): int
    {
        $idx = min(max($stage, 1) - 1, count(self::BACKOFF_STEPS) - 1);

        return self::BACKOFF_STEPS[$idx];
    }

    /**
     * Current state considering Open-TTL expiry.
     *
     * When the state key has expired (CB was Open, backoff elapsed) — return half-open:
     * allow one probe task. Probe success → Closed, failure → Open (stage +1).
     */
    private function getRawState(string $botId): ?string
    {
        $value = $this->cache->get(self::STATE_KEY_PREFIX.$botId);

        if ($value === null || $value === false) {
            // No key. If backoff stage > 0 — CB was open and backoff elapsed → half-open.
            // Otherwise — breaker never tripped (defaults to closed).
            $stage = (int) $this->cache->get(self::BACKOFF_KEY_PREFIX.$botId);

            return $stage > 0 ? 'half-open' : 'closed';
        }

        return (string) $value;
    }
}
