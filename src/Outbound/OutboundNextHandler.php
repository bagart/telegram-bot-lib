<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundNextHandlerContract;

/**
 * One middleware plus the rest of the chain. Linked-list node built by
 * {@see OutboundPipeline::execute()} — named in stack traces instead of `{closure}`.
 */
final class OutboundNextHandler implements OutboundNextHandlerContract
{
    public function __construct(
        private readonly OutboundMiddleware $middleware,
        private readonly OutboundNextHandlerContract $next,
    ) {
    }

    public function handle(OutboundEnvelope $envelope): void
    {
        $this->middleware->handle($envelope, $this->next);
    }
}
