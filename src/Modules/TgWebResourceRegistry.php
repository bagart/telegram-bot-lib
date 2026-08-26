<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Append-only store of resource provider class-strings declared by modules.
 * State-pure: class-strings only, no behavior and no menu-domain knowledge.
 */
final class TgWebResourceRegistry
{
    /** @var list<array{module: string, class: class-string}> */
    private array $entries = [];

    /** @param class-string $providerClass */
    public function add(string $moduleId, string $providerClass): void
    {
        $this->entries[] = ['module' => $moduleId, 'class' => $providerClass];
    }

    /** @return list<array{module: string, class: class-string}> */
    public function all(): array
    {
        return $this->entries;
    }
}
