<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\CircuitBreakerState;

/**
 * Per-bot circuit breaker contract.
 *
 * After N consecutive errors of the same type (network timeout, Redis unavailable,
 * Telegram 5xx) the breaker transitions to Open — Worker does not fetch new bot tasks
 * for the backoff duration (1s → 5s → 30s → 5min). After backoff — one probe task (HalfOpen);
 * success → Closed, failure → Open.
 *
 * Implementation uses {@see OutboundCacheContract::incrementWithTtl()} — atomic,
 * without get+put race condition. See todo.md §6.2.
 */
interface OutboundCircuitBreakerContract
{
    /**
     * Whether task execution is allowed for the bot right now.
     *
     * @param  string  $botId  Bot identifier.
     */
    public function allowsRequest(string $botId): bool;

    /**
     * Register a bot task processing failure (increment counter).
     */
    public function recordFailure(string $botId): void;

    /**
     * Register a bot task processing success (reset counter).
     */
    public function recordSuccess(string $botId): void;

    /**
     * Current breaker state for the bot.
     */
    public function getState(string $botId): CircuitBreakerState;
}
