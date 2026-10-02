<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Modules;

/**
 * Per-module settings, resolved with the same inheritance chain as
 * enablement: chat override → bot default → platform.
 *
 * Reads (`settingsFor`) return the effective (inherited) map; enumeration
 * (`chatsWithSettings`) returns explicitly stored per-scope maps so callers
 * never materialize inherited values back into a narrower scope on save.
 * Writes (`patchSettings`) merge into the raw stored map at exactly one
 * scope and are driver-agnostic (legacy table or engine activation row).
 */
interface ModuleSettingsContract
{
    /**
     * Effective settings map for a module in a scope.
     *
     * `$chatId === null` resolves the bot scope (platform → bot).
     * A chat scope layers chat overrides over the bot-scope values;
     * descriptor defaults remain the caller's fallback.
     *
     * @return array<string, mixed>
     */
    public function settingsFor(string $moduleId, string $botId, ?int $chatId = null): array;

    /**
     * Merge `$patch` into the stored settings at exactly one scope.
     *
     * Semantics:
     * - a `key => null` entry REMOVES that key (inherit from wider scope);
     * - last-write-wins, no CAS (in-chat/CLI writes);
     * - the reserved key `enabled` (bool) does not land in settings — it
     *   flips chat-level enablement when `$chatId !== null`, bot-level
     *   enablement when `$chatId === null`;
     * - the merge reads the RAW stored map at this scope, never the
     *   inherited `settingsFor()` result.
     *
     * @param array<string, mixed|null> $patch
     */
    public function patchSettings(string $moduleId, string $botId, ?int $chatId, array $patch): void;

    /**
     * Explicit (non-inherited) stored settings for every scope a module
     * uses, newest chat scopes first by chat id.
     *
     * @param string|null $botId restrict to one bot; null = all bots
     * @return list<array{botId: string, chatId: int|null, settings: array<string, mixed>}>
     *         `chatId === null` is the bot scope.
     */
    public function chatsWithSettings(string $moduleId, ?string $botId = null): array;
}
