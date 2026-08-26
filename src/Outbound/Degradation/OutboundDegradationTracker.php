<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Degradation;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundCacheContract;
use BAGArt\TelegramBot\Outbound\CircuitBreakerState;

/**
 * Tracks the aggregate outbound degradation state (06 §43) in the shared
 * cache so any process can observe it.
 *
 * Fed by the daemon once per tick. Worsening transitions apply immediately
 * (circuit breaker open anywhere → Degraded; $queueFailureThreshold
 * consecutive queue-driver failures → Offline). Recovery is hysteretic:
 * the state may only step down after $recoverStreakSec without worsening
 * input.
 */
final class OutboundDegradationTracker
{
    private const string KEY_STATE = 'tg_outbound:degradation:state';

    private const string KEY_SINCE = 'tg_outbound:degradation:since';

    private const string KEY_FAILURES = 'tg_outbound:degradation:queue_failures';

    private const string KEY_LAST_CHANGE = 'tg_outbound:degradation:last_change';

    private const int TTL_SEC = 86400;

    public function __construct(
        private readonly OutboundCacheContract $cache,
        private readonly int $queueFailureThreshold = 3,
        private readonly int $recoverStreakSec = 60,
    ) {
    }

    /**
     * Feed one observation cycle; returns the (possibly new) current state.
     *
     * @param  list<CircuitBreakerState>  $botBreakerStates  Breaker state per tracked bot.
     */
    public function observe(array $botBreakerStates, bool $queueHealthy, int $now): OutboundDegradationState
    {
        $current = $this->state();

        $failures = $queueHealthy ? 0 : $this->incrementFailures();
        $breakerOpen = in_array(CircuitBreakerState::Open, $botBreakerStates, true);

        $desired = match (true) {
            $failures >= $this->queueFailureThreshold => OutboundDegradationState::Offline,
            $breakerOpen => OutboundDegradationState::Degraded,
            default => OutboundDegradationState::Normal,
        };

        if ($desired === $current) {
            return $current;
        }

        if ($desired->isWorseThan($current)) {
            return $this->transition($desired, $now);
        }

        $lastChange = (int) ($this->get(self::KEY_LAST_CHANGE) ?? 0);
        if ($now - $lastChange < $this->recoverStreakSec) {
            return $current;
        }

        return $this->transition($desired, $now);
    }

    public function state(): OutboundDegradationState
    {
        $raw = $this->get(self::KEY_STATE);

        return $raw === null ? OutboundDegradationState::Normal : OutboundDegradationState::from($raw);
    }

    /** Unix timestamp when the current state was entered (null = always Normal). */
    public function since(): ?int
    {
        $raw = $this->get(self::KEY_SINCE);

        return $raw === null ? null : (int) $raw;
    }

    private function incrementFailures(): int
    {
        return $this->cache->incrementWithTtl(self::KEY_FAILURES, 1, self::TTL_SEC);
    }

    private function transition(OutboundDegradationState $next, int $now): OutboundDegradationState
    {
        if ($next !== OutboundDegradationState::Offline) {
            // Leaving Offline resets the failure accumulator that forced it.
            $this->cache->forget(self::KEY_FAILURES);
        }
        $this->cache->put(self::KEY_STATE, $next->value, self::TTL_SEC);
        $this->cache->put(self::KEY_SINCE, $now, self::TTL_SEC);
        $this->cache->put(self::KEY_LAST_CHANGE, $now, self::TTL_SEC);

        return $next;
    }

    private function get(string $key): mixed
    {
        return $this->cache->get($key);
    }
}
