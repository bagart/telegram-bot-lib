<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed;

use BAGArt\TelegramBot\Modules\Attributes\TgMiddlewareAttribute;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use Closure;

#[TgMiddlewareAttribute]
class AttributedPassMiddleware implements OutboundMiddleware
{
    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        $next($envelope);
    }
}
