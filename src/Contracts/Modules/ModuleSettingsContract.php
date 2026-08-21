<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Modules;

/**
 * Per-module settings stored on enablement rows, resolved with the same
 * inheritance chain as enablement: chat override → bot default → platform.
 */
interface ModuleSettingsContract
{
    /**
     * Effective settings map for a module in a (bot, chat) scope.
     *
     * @return array<string, mixed>
     */
    public function settingsFor(string $moduleId, string $botId, int $chatId): array;
}
