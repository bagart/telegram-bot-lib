<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Modules;

/**
 * Resolves "is this module enabled for this (bot, chat) right now?".
 * Implementations MUST cache results — called from the update selector
 * for every incoming DTO (NFR-5: 0 SQL in steady state).
 */
interface ModuleEnablementContract
{
    /**
     * @param  int|null  $chatId  chat scope; null = bot-level decision only
     *                       (bot/platform rows + descriptor defaults — used by
     *                       selector dispatch for command and chat-member
     *                       updates, Q11-D1).
     */
    public function isEnabled(string $moduleId, string $botId, ?int $chatId = null): bool;

    /**
     * Invalidate cached enablement after an admin toggle.
     * Null chat = bot level and below; null bot = platform scope (every
     * cached decision map may embed platform rows). Cross-process staleness
     * for non-exact scopes is bounded by the implementation TTL — mutations
     * never consume stale reads, and the menu epoch vector compensates at the
     * bootstrap layer (§15.2 of the menu RFC).
     */
    public function refresh(
        ?string $botId = null,
        ?int $chatId = null,
    ): void;
}
