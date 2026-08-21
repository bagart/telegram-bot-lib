<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Holds descriptors of discovered modules — "what is installed and discovered".
 * Knows nothing about enablement.
 */
class TgModuleRegistry
{
    /** @var array<string, TgModuleDescriptor> keyed by module id */
    private array $descriptors = [];

    public function add(TgModuleDescriptor $descriptor): bool
    {
        if (isset($this->descriptors[$descriptor->id])) {
            return false;
        }

        $this->descriptors[$descriptor->id] = $descriptor;

        return true;
    }

    public function has(string $moduleId): bool
    {
        return isset($this->descriptors[$moduleId]);
    }

    public function get(string $moduleId): ?TgModuleDescriptor
    {
        return $this->descriptors[$moduleId] ?? null;
    }

    /** @return list<string> */
    public function moduleIds(): array
    {
        return array_keys($this->descriptors);
    }

    /** @return list<TgModuleDescriptor> */
    public function all(): array
    {
        return array_values($this->descriptors);
    }

    /**
     * Descriptor-declared default of a module; null when the module is not discovered.
     */
    public function defaultEnabledOf(string $moduleId): ?bool
    {
        return $this->descriptors[$moduleId]?->defaultEnabled;
    }

    /**
     * Fail policy on enablement-storage errors (Q-D2); null when the module
     * is not discovered.
     */
    public function failClosedOf(string $moduleId): ?bool
    {
        return $this->descriptors[$moduleId]?->failClosed;
    }
}
