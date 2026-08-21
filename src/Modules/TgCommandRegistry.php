<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;

/**
 * Registry of bot commands declared by modules: "/<name>" in a message text
 * routes exclusively to the declared processor (bypassing the regular
 * message processors for that update). Class-strings only — instances are
 * built lazily by the update selector via TgTypeDTOProcessorContract::build().
 */
class TgCommandRegistry
{
    /** @var array<string, class-string<TgTypeDTOProcessorContract>> */
    private array $commands = [];

    /**
     * @param  class-string<TgTypeDTOProcessorContract>  $processorClass
     */
    public function register(string $name, string $processorClass): self
    {
        $this->commands[ltrim($name, '/')] = $processorClass;

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->commands[ltrim($name, '/')]);
    }

    /**
     * @return class-string<TgTypeDTOProcessorContract>|null
     */
    public function processorOf(string $name): ?string
    {
        return $this->commands[ltrim($name, '/')] ?? null;
    }

    /**
     * @return array<string, class-string<TgTypeDTOProcessorContract>>
     */
    public function commands(): array
    {
        return $this->commands;
    }

    /**
     * Extract the command name from a message text: "/cmd@botname arg" → "cmd".
     * Returns null when the text is not a slash command.
     */
    public static function parseCommandName(string $text): ?string
    {
        if (!preg_match('/^\/([A-Za-z0-9_]+)(@[A-Za-z0-9_]+)?(\s|$)/', $text, $m)) {
            return null;
        }

        return $m[1];
    }
}
