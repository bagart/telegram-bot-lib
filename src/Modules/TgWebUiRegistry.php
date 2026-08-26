<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Append-only store of UI manifest class-strings declared by modules.
 * State-pure: class-strings only, no behavior and no menu-domain knowledge —
 * contract refinement happens where the contracts are visible.
 */
final class TgWebUiRegistry
{
    /** @var list<array{module: string, class: class-string}> */
    private array $entries = [];

    /** @param class-string $uiClass */
    public function add(string $moduleId, string $uiClass): void
    {
        $this->entries[] = ['module' => $moduleId, 'class' => $uiClass];
    }

    /** @return list<array{module: string, class: class-string}> */
    public function all(): array
    {
        return $this->entries;
    }
}
