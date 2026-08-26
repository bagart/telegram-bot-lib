<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Modules;

/**
 * Bot-scoped command route overrides: maps a slash command to a processor
 * class for a specific bot, as declared in the module engine's routing
 * table. Implementations must return null when no route exists so the flat
 * command registry stays the fallback — the contract never throws for
 * "route not found".
 */
interface CommandRouteContract
{
    /**
     * Processor class FQN handling $commandName for $botId, or null when the
     * routing table has no usable entry for it.
     */
    public function processorOf(string $commandName, string $botId): ?string;
}
