<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules\Attributes;

use Attribute;

/**
 * Declares the class as an outbound middleware. The class must still
 * implement OutboundMiddleware — the attribute is sugar over
 * TgModuleRegistrar::outboundMiddleware(), not a contract replacement.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TgMiddlewareAttribute
{
}
