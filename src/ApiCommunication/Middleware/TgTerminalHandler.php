<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

/**
 * End of the middleware chain — delegates to the core executor callable the
 * pipeline was invoked with. The callable is supplied once per execute() call
 * by the pipeline owner, not built inline during chain assembly.
 */
final class TgTerminalHandler implements TgNextHandlerContract
{
    public function __construct(
        private readonly \Closure $core,
    ) {}

    public static function fromCallable(callable $core): self
    {
        return new self(\Closure::fromCallable($core));
    }

    public function handle(TgEnvelope $env): mixed
    {
        return ($this->core)($env);
    }
}
