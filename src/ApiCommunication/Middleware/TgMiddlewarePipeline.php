<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

final class TgMiddlewarePipeline
{
    /** @var TgMiddlewareContract[] */
    private array $middlewares = [];

    public function add(TgMiddlewareContract $mw): self
    {
        $this->middlewares[] = $mw;

        return $this;
    }

    public function execute(TgEnvelope $env, callable $core): mixed
    {
        $runner = array_reduce(
            array_reverse($this->middlewares),
            static fn ($next, TgMiddlewareContract $mw): callable =>
                static fn (TgEnvelope $env): mixed => $mw->handle($env, $next),
            $core,
        );

        return $runner($env);
    }
}
