<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Append-only store of permission resolver class-strings declared by modules
 * (raw store; the richer module-owned permission contract is refined and
 * enforced by the UI host module, not here).
 */
final class TgWebPermissionRegistry
{
    /** @var list<array{module: string, class: class-string}> */
    private array $entries = [];

    /** @param class-string $resolverClass */
    public function add(string $moduleId, string $resolverClass): void
    {
        $this->entries[] = ['module' => $moduleId, 'class' => $resolverClass];
    }

    /** @return list<array{module: string, class: class-string}> */
    public function all(): array
    {
        return $this->entries;
    }
}
