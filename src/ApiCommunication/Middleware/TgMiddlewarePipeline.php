<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

/**
 * Telegram API middleware pipeline (PSR-15 style, mixed return).
 *
 * Chain is folded right-to-left into a linked list of named
 * {@see TgNextHandler} nodes terminated by a {@see TgTerminalHandler}
 * wrapping the caller-supplied core executor.
 *
 * Call order = order in the middleware list (first = outermost, last = core).
 */
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
        $handler = TgTerminalHandler::fromCallable($core);

        foreach (array_reverse($this->middlewares) as $middleware) {
            $handler = new TgNextHandler($middleware, $handler);
        }

        return $handler->handle($env);
    }
}
