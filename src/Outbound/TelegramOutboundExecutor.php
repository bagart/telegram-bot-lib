<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiConflictException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgBadRequestException;
use Closure;
use Throwable;

/**
 * Final middleware of the outbound pipeline — sending to Telegram API (todo.md §3.6).
 *
 * Deserializes DTO from envelope, resolves bot token (tokens in DB, not in .env),
 * makes a synchronous request via {@see TgBotApiDTOClientContract}.
 *
 * Error classification — inline (todo.md §9 principle 8: NO separate ErrorClassifier —
 * executor is last in the chain, classification is its own logic). Each error
 * maps to a control exception ({@see OutboundRetryException} /
 * {@see OutboundBusinessErrorException}) which the Worker catches (Phase 4).
 *
 * Key bug fixes (todo.md §0.5):
 *   - retryAfter is read from EXCEPTION ({@see TgApiRateLimitException::getRetryAfter()}),
 *     NOT from TgApiResponse (response->retryAfter is always null on 429).
 *   - registerRetryAfter is called (dead code before — the method existed but was
 *     never called; now the rate limiter receives the actual retry_after from Telegram).
 *
 * Success = return (we do NOT call $next — executor is final in the chain).
 *
 * Fifth/last middleware in the chain (todo.md §3.2).
 */
final class TelegramOutboundExecutor implements OutboundMiddleware
{
    public function __construct(
        private readonly TgBotApiDTOClientContract $dtoClient,
        private readonly OutboundRateLimiterContract $rateLimiter,
        private readonly TgApiDTOMapperContract $dtoMapper,
    ) {
    }

    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        $dto = $this->dtoMapper->fromArray($envelope->task->dtoClass, $envelope->task->dtoData);

        try {
            $this->dtoClient->request(
                botConfig: $envelope->task->botConfig,
                dto: $dto,
            );
            // Success — $next not called (last in chain).
        } catch (Throwable $e) {
            // retryAfter is read from EXCEPTION, not from response (response->retryAfter is always null on 429).
            throw $this->classifyException($e, $envelope);
        }
    }

    /**
     * Inline classification of Telegram API response/error → control exception.
     *
     * @return Throwable One of OutboundRetryException / OutboundBusinessErrorException.
     */
    private function classifyException(Throwable $e, OutboundEnvelope $envelope): Throwable
    {
        $key = $this->buildRateLimitKey($envelope);

        return match (true) {
            $e instanceof TgApiRateLimitException => $this->rateLimit429($e, $key),
            $e instanceof TgApiConflictException => new OutboundRetryException(
                delaySec: 5,
                reason: 'telegram_conflict',
                previous: $e
            ),
            $e instanceof TgApiNetworkException => new OutboundRetryException(
                delaySec: 10,
                reason: 'network_timeout',
                previous: $e
            ),
            $e instanceof TgBadRequestException => new OutboundBusinessErrorException(
                reason: 'bad_request',
                context: ['msg' => $e->getMessage()],
                previous: $e
            ),
            default => new OutboundRetryException(delaySec: 10, reason: 'unknown_transport_error', previous: $e),
        };
    }

    /**
     * Handle 429: register retry_after in rate limiter (dead code bug fix),
     * return retry with delay from exception.
     */
    private function rateLimit429(TgApiRateLimitException $e, string $key): OutboundRetryException
    {
        $retryAfter = $e->getRetryAfter() ?? 30;
        $this->rateLimiter->registerRetryAfter($key, (float)$retryAfter);

        return new OutboundRetryException(delaySec: $retryAfter, reason: 'telegram_rate_limit', previous: $e);
    }

    /**
     * Rate-limit key — must match RateLimitMiddleware::buildKey
     * so that registerRetryAfter here and getRetryDelay there refer to the same bucket.
     * Format: {botId}:{dtoMethod}:{orderingKey} (parsed by TgAdvancedRateLimiter).
     */
    private function buildRateLimitKey(OutboundEnvelope $envelope): string
    {
        $botId = $envelope->task->botConfig->botId;
        $dtoMethod = $this->shortMethod($envelope->task->dtoClass);
        $orderingKey = $envelope->task->orderingKey ?? 'global';

        return "{$botId}:{$dtoMethod}:{$orderingKey}";
    }

    private function shortMethod(string $dtoClass): string
    {
        $pos = strrpos($dtoClass, '\\');

        return $pos === false ? $dtoClass : substr($dtoClass, $pos + 1);
    }
}
