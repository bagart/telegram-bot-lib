<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\OutboundEnvelope;

/**
 * Next-hop node of the outbound middleware chain (PSR-15-style handler object).
 *
 * Each {@see \BAGArt\TelegramBot\Outbound\OutboundMiddleware} receives the
 * rest of the chain as this typed contract instead of an untyped closure;
 * stack traces and IDE navigation show the chain nodes by name.
 */
interface OutboundNextHandlerContract
{
    /**
     * @throws \BAGArt\TelegramBot\Outbound\OutboundRetryException Middleware requested a retry.
     * @throws \BAGArt\TelegramBot\Outbound\OutboundSkipException Middleware decided to discard the task.
     * @throws \BAGArt\TelegramBot\Outbound\OutboundBusinessErrorException Middleware recorded a business error.
     */
    public function handle(OutboundEnvelope $envelope): void;
}
