<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

final class CircuitBreakerMiddleware implements TgMiddlewareContract
{
    public function handle(TgEnvelope $env, callable $next): mixed
    {
        // simplified placeholder
        return $next($env);
    }
}
