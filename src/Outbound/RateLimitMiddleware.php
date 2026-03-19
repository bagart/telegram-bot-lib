<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;
use Closure;

/**
 * Middleware for rate limit checking (todo.md §3.4, §5.4).
 *
 * Queries {@see OutboundRateLimiterContract::getRetryDelay()} by the task key.
 * If limit is active (delay > 0) — the task is postponed by delay seconds
 * ({@see OutboundRetryException} reason='rate_limit').
 *
 * Key format: {botId}:{dtoMethod}:{orderingKey} — parsed by TgAdvancedRateLimiter:
 *   parts[0] = botId (for namespacing cache keys tg_limit:{botId}:...),
 *   parts[1] = method (ignored by the parser, but included in flood_key),
 *   parts[2] = chatId (for per-chat pacing).
 *
 * Third middleware in the chain (todo.md §3.2) — BEFORE Ordering: rate-limit check
 * is lightweight (1 cache read), does not require holding the ordering lock.
 */
final class RateLimitMiddleware implements OutboundMiddleware
{
    public function __construct(
        private readonly OutboundRateLimiterContract $limiter,
    ) {
    }

    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        $key = $this->buildKey($envelope);
        $delay = $this->limiter->getRetryDelay($key);

        if ($delay > 0.0) {
            throw new OutboundRetryException(
                delaySec: (int) ceil($delay),
                reason: 'rate_limit',
            );
        }

        $next($envelope);
    }

    /**
     * Rate-limit key: {botId}:{dtoMethod}:{orderingKey}.
     *
     * dtoMethod — short method name from FQCN dtoClass (readable in metrics;
     * TgAdvancedRateLimiter parser ignores parts[1], but flood_key uses the full key).
     * orderingKey — ordering key (typically chat_id or chat_id:session), fallback 'global'.
     */
    private function buildKey(OutboundEnvelope $envelope): string
    {
        $botId = $envelope->task->botConfig->botId;
        $dtoMethod = $this->shortMethod($envelope->task->dtoClass);
        $orderingKey = $envelope->task->orderingKey ?? 'global';

        return "{$botId}:{$dtoMethod}:{$orderingKey}";
    }

    private function shortMethod(string $dtoClass): string
    {
        // FQCN → basename (App\TgApi\Methods\DTO\SendMessageDTO → SendMessageDTO).
        $pos = strrpos($dtoClass, '\\');

        return $pos === false ? $dtoClass : substr($dtoClass, $pos + 1);
    }
}
