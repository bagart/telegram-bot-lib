<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Append-only store of module audit-event class-strings declared via
 * TgModuleRegistrar::auditEvent(). State-pure: class-strings only, no
 * behavior and no audit-domain knowledge — contract refinement happens
 * at consumption (audit provider).
 */
final class TgAuditEventRegistry
{
    /** @var list<array{module: string, class: class-string}> */
    private array $entries = [];

    /** @param class-string $eventClass */
    public function add(string $moduleId, string $eventClass): void
    {
        $this->entries[] = ['module' => $moduleId, 'class' => $eventClass];
    }

    /** @return list<array{module: string, class: class-string}> */
    public function all(): array
    {
        return $this->entries;
    }
}
