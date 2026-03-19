<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

/**
 * Circuit breaker state for a single bot.
 *
 * - Closed   — normal mode, requests allowed, errors are counted.
 * - Open     — consecutive error threshold exceeded, new tasks are deferred.
 * - HalfOpen — backoff elapsed, one probe task allowed; success → Closed, failure → Open.
 */
enum CircuitBreakerState: string
{
    case Closed = 'closed';
    case Open = 'open';
    case HalfOpen = 'half-open';
}
