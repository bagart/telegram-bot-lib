<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Readonly metadata of a discovered module. Lives in process memory only —
 * never persisted to queues or caches with behavior attached.
 */
final readonly class TgModuleDescriptor
{
    /**
     * @param  string  $id  unique logical id, the enablement-table key
     * @param  array<string, string>  $requiresModules  module dependencies (id => version constraint), model only
     * @param  list<string>  $conflictsWith  module ids this module conflicts with, model only
     * @param  list<TgModuleCapability>  $capabilities  component kinds the module provides
     * @param  bool  $defaultEnabled  last resort of the enablement inheritance chain
     * @param  bool  $failClosed  on enablement-storage DB error: true = treat the module as disabled, false (default) = fall back to defaultEnabled (fail-open, Q-D2)
     * @param  list<string>  $requiresCapabilities  capability ids this module needs from other
     *                       modules; missing required ones block activation, engine-side optional
     *                       requirements stay expressible via the capability resolver input
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public array $requiresModules = [],
        public array $conflictsWith = [],
        public array $capabilities = [],
        public bool $defaultEnabled = true,
        public bool $failClosed = false,
        public array $requiresCapabilities = [],
    ) {
    }
}
