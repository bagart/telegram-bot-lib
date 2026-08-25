<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Middleware;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgEnvelope;

/**
 * Next-hop node of the Telegram API middleware chain (PSR-15-style handler
 * object). Middlewares receive the rest of the chain as this typed contract
 * instead of an untyped callable.
 */
interface TgNextHandlerContract
{
    public function handle(TgEnvelope $env): mixed;
}
