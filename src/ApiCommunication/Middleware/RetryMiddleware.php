<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

final class RetryMiddleware implements TgMiddlewareContract
{
    public function __construct(
        private int $maxRetries = 2,
    ) {
    }

    public function handle(TgEnvelope $env, callable $next): mixed
    {
        $i = 0;

        retry:
        try {
            return $next($env);
        } catch (\Throwable $e) {
            if (++$i <= $this->maxRetries) {
                goto retry;
            }

            throw $e;
        }
    }
}
