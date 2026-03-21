<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

/**
 * Circuit breaker pattern for Telegram API calls.
 */
interface TgCircuitBreakerContract
{
    /**
     * Check if the method can be executed.
     */
    public function canExecute(string $method): bool;

    /**
     * Record a successful call.
     */
    public function recordSuccess(string $method): void;

    /**
     * Record a failed call.
     */
    public function recordFailure(string $method): void;

    /**
     * Reset all circuit breaker state.
     */
    public function reset(): void;
}
