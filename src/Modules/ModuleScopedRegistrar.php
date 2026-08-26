<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

use LogicException;

/**
 * Module-boot-scoped registrar decorator: stamps the booting module's
 * descriptor id onto every web* declaration (D38) and proxies all other
 * registrar methods to the inner registrar untouched.
 */
final class ModuleScopedRegistrar implements TgModuleRegistrar
{
    public function __construct(
        private readonly TgModuleRegistrar $inner,
        private readonly string $moduleId,
        private readonly ?TgWebUiRegistry $webUiRegistry = null,
        private readonly ?TgWebApiRegistry $webApiRegistry = null,
        private readonly ?TgWebResourceRegistry $webResourceRegistry = null,
        private readonly ?TgWebPermissionRegistry $webPermissionRegistry = null,
    ) {
    }

    public function processor(string $dtoClass, string $processorClass): self
    {
        $this->inner->processor($dtoClass, $processorClass);

        return $this;
    }

    public function validationRule(string $ruleClass, int $weight = 0): self
    {
        $this->inner->validationRule($ruleClass, $weight);

        return $this;
    }

    public function outboundMiddleware(string $middlewareClass): self
    {
        $this->inner->outboundMiddleware($middlewareClass);

        return $this;
    }

    public function command(string $name, string $processorClass): self
    {
        $this->inner->command($name, $processorClass);

        return $this;
    }

    public function registerAttributed(string $providerClass): self
    {
        $this->inner->registerAttributed($providerClass);

        return $this;
    }

    public function webUi(string $uiClass): self
    {
        $this->addScoped($this->webUiRegistry, $uiClass, 'webUi() requires a TgWebUiRegistry');

        return $this;
    }

    public function webApi(string $handlerClass): self
    {
        $this->addScoped($this->webApiRegistry, $handlerClass, 'webApi() requires a TgWebApiRegistry');

        return $this;
    }

    public function webResource(string $providerClass): self
    {
        $this->addScoped($this->webResourceRegistry, $providerClass, 'webResource() requires a TgWebResourceRegistry');

        return $this;
    }

    public function webPermissions(string $resolverClass): self
    {
        $this->addScoped($this->webPermissionRegistry, $resolverClass, 'webPermissions() requires a TgWebPermissionRegistry');

        return $this;
    }

    private function addScoped(TgWebUiRegistry|TgWebApiRegistry|TgWebResourceRegistry|TgWebPermissionRegistry|null $registry, string $class, string $requirement): void
    {
        if (! class_exists($class)) {
            throw new LogicException("$class does not exist");
        }

        if ($registry === null) {
            throw new LogicException($requirement." — construct ModuleScopedRegistrar with one to register '$this->moduleId' declarations.");
        }

        $registry->add($this->moduleId, $class);
    }
}
