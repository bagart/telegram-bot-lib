<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

final class RateLimitMiddleware implements TgMiddlewareContract
{
    public function handle(TgEnvelope $env, callable $next): mixed
    {
        // token bucket / limiter hook
        return $next($env);
    }
}
