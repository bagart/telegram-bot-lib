<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundNextHandlerContract;

/**
 * End of the middleware chain — reached only when every middleware passed the
 * envelope forward. The outbound pipeline's terminal executor is last in the
 * chain and does not call $next, so this node is a deliberate no-op.
 */
final class OutboundTerminalHandler implements OutboundNextHandlerContract
{
    public function handle(OutboundEnvelope $envelope): void
    {
        unset($envelope);
    }
}
