<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use Throwable;

/**
 * Boots discovered modules: reads descriptors into TgModuleRegistry and
 * registers components via TgModuleRegistrar.
 *
 * Fault isolation (§6.1 of the modularity RFC): discovery and registration
 * errors of one module are logged and the module is skipped; runtime errors
 * inside processors are NOT caught here — they propagate as normal
 * application errors.
 */
class ModuleBootloader
{
    /** @var array<string, bool> module ids already booted */
    private array $booted = [];

    /** @var list<string> module ids whose register() threw */
    private array $failed = [];

    public function __construct(
        private readonly TgModuleRegistrar $registrar,
        private readonly TgModuleRegistry $registry,
        private readonly ASKLogWrapper $logger,
        private readonly ?TgWebUiRegistry $webUiRegistry = null,
        private readonly ?TgWebApiRegistry $webApiRegistry = null,
        private readonly ?TgWebResourceRegistry $webResourceRegistry = null,
        private readonly ?TgWebPermissionRegistry $webPermissionRegistry = null,
    ) {
    }

    /**
     * Module ids whose register() threw during boot (components skipped,
     * descriptor retained). Consumed by UI-host refinement and doctor tooling.
     *
     * @return list<string>
     */
    public function failed(): array
    {
        return $this->failed;
    }

    /**
     * @param  list<class-string<TgModuleContract>>  $providerClasses
     * @return list<string> ids of successfully booted modules
     */
    public function bootAll(array $providerClasses): array
    {
        $booted = [];

        foreach ($providerClasses as $providerClass) {
            $id = $this->bootOne($providerClass);
            if ($id !== null) {
                $booted[] = $id;
            }
        }

        return $booted;
    }

    /**
     * @param  class-string<TgModuleContract>  $providerClass
     * @return string|null booted module id, null when skipped
     */
    public function bootOne(string $providerClass): ?string
    {
        if (!is_a($providerClass, TgModuleContract::class, true)) {
            $this->logger->error('Module boot skipped: provider does not implement TgModuleContract', [
                'provider' => $providerClass,
            ]);

            return null;
        }

        try {
            $descriptor = $providerClass::descriptor();
        } catch (Throwable $e) {
            $this->logger->error('Module discovery failed, skipping', [
                'provider' => $providerClass,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (isset($this->booted[$descriptor->id])) {
            return null;
        }

        if (!$this->registry->add($descriptor)) {
            $this->logger->warning('Duplicate module id skipped: first registered wins', [
                'module' => $descriptor->id,
                'provider' => $providerClass,
            ]);

            return null;
        }

        try {
            $scoped = new ModuleScopedRegistrar(
                inner: $this->registrar,
                moduleId: $descriptor->id,
                webUiRegistry: $this->webUiRegistry,
                webApiRegistry: $this->webApiRegistry,
                webResourceRegistry: $this->webResourceRegistry,
                webPermissionRegistry: $this->webPermissionRegistry,
            );
            $providerClass::register($scoped);
        } catch (Throwable $e) {
            $this->failed[] = $descriptor->id;
            $this->logger->error('Module registration failed, module skipped', [
                'module' => $descriptor->id,
                'provider' => $providerClass,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $this->booted[$descriptor->id] = true;

        return $descriptor->id;
    }
}
