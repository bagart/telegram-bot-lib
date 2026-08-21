<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Entry point of a plugin. Static by design: discovery reads metadata and
 * registers components without creating a stateful module instance.
 */
interface TgModuleContract
{
    /** Pure metadata: id, version, name, dependencies, capabilities. No side effects. */
    public static function descriptor(): TgModuleDescriptor;

    /** Declares components into registries exposed by $registrar. Once per boot. Idempotent. */
    public static function register(TgModuleRegistrar $registrar): void;
}
