<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

/**
 * One middleware plus the rest of the chain. Linked-list node built by
 * {@see TgMiddlewarePipeline::execute()} — named in stack traces instead of `{closure}`.
 */
final class TgNextHandler implements TgNextHandlerContract
{
    public function __construct(
        private readonly TgMiddlewareContract $middleware,
        private readonly TgNextHandlerContract $next,
    ) {
    }

    public function handle(TgEnvelope $env): mixed
    {
        return $this->middleware->handle($env, $this->next);
    }
}
