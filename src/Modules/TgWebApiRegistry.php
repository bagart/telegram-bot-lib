<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Append-only store of web API handler class-strings declared by modules.
 * State-pure: class-strings only, no behavior and no menu-domain knowledge.
 */
final class TgWebApiRegistry
{
    /** @var list<array{module: string, class: class-string}> */
    private array $entries = [];

    /** @param class-string $handlerClass */
    public function add(string $moduleId, string $handlerClass): void
    {
        $this->entries[] = ['module' => $moduleId, 'class' => $handlerClass];
    }

    /** @return list<array{module: string, class: class-string}> */
    public function all(): array
    {
        return $this->entries;
    }
}
