<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Modules\ModuleScopedRegistrar;
use BAGArt\TelegramBot\Modules\TgWebApiRegistry;
use BAGArt\TelegramBot\Modules\TgWebPermissionRegistry;
use BAGArt\TelegramBot\Modules\TgWebResourceRegistry;
use BAGArt\TelegramBot\Modules\TgWebUiRegistry;

describe('ModuleScopedRegistrar web* stamping', function () {
    it('stamps the module id for each of the four web methods', function () {
        $ui = new TgWebUiRegistry();
        $api = new TgWebApiRegistry();
        $resource = new TgWebResourceRegistry();
        $permissions = new TgWebPermissionRegistry();

        $scoped = new ModuleScopedRegistrar(
            inner: new TypedModuleRegistrarDouble(),
            moduleId: 'mafia',
            webUiRegistry: $ui,
            webApiRegistry: $api,
            webResourceRegistry: $resource,
            webPermissionRegistry: $permissions,
        );

        $scoped
            ->webUi(ScopedFixtureUi::class)
            ->webApi(ScopedFixtureApi::class)
            ->webResource(ScopedFixtureResource::class)
            ->webPermissions(ScopedFixturePermissions::class);

        expect($ui->all())->toBe([['module' => 'mafia', 'class' => ScopedFixtureUi::class]]);
        expect($api->all())->toBe([['module' => 'mafia', 'class' => ScopedFixtureApi::class]]);
        expect($resource->all())->toBe([['module' => 'mafia', 'class' => ScopedFixtureResource::class]]);
        expect($permissions->all())->toBe([['module' => 'mafia', 'class' => ScopedFixturePermissions::class]]);
    });

    it('keeps entries append-only: duplicates are not deduped here (D34 refines)', function () {
        $ui = new TgWebUiRegistry();

        $scoped = new ModuleScopedRegistrar(new TypedModuleRegistrarDouble(), 'dup-module', webUiRegistry: $ui);
        $scoped->webUi(ScopedFixtureUi::class);
        $scoped->webUi(ScopedFixtureUi::class);

        expect($ui->all())->toHaveCount(2);
    });

    it('preserves registration order across modules', function () {
        $ui = new TgWebUiRegistry();

        (new ModuleScopedRegistrar(new TypedModuleRegistrarDouble(), 'alpha', webUiRegistry: $ui))->webUi(ScopedFixtureUi::class);
        (new ModuleScopedRegistrar(new TypedModuleRegistrarDouble(), 'beta', webUiRegistry: $ui))->webUi(ScopedFixtureApi::class);

        expect(array_column($ui->all(), 'module'))->toBe(['alpha', 'beta']);
    });

    it('rejects unknown classes with LogicException', function () {
        $scoped = new ModuleScopedRegistrar(new TypedModuleRegistrarDouble(), 'x', webUiRegistry: new TgWebUiRegistry());

        $scoped->webUi('App\\Does\\Not\\Exist');
    })->throws(LogicException::class);

    it('refuses web declarations when the registry is missing', function () {
        (new ModuleScopedRegistrar(new TypedModuleRegistrarDouble(), 'x'))->webUi(ScopedFixtureUi::class);
    })->throws(LogicException::class);
});

describe('ModuleScopedRegistrar delegation', function () {
    it('proxies non-web methods to the inner registrar untouched', function () {
        $inner = new TypedModuleRegistrarDouble();
        $scoped = new ModuleScopedRegistrar($inner, 'delegator');

        $scoped
            ->processor('dto', 'proc')
            ->validationRule('rule', 5)
            ->outboundMiddleware('mw')
            ->command('ping', 'proc')
            ->registerAttributed('provider');

        expect($inner->calls)->toBe([
            ['processor', 'dto', 'proc'],
            ['validationRule', 'rule', 5],
            ['outboundMiddleware', 'mw'],
            ['command', 'ping', 'proc'],
            ['registerAttributed', 'provider'],
        ]);
    });
});

class TypedModuleRegistrarDouble implements BAGArt\TelegramBot\Modules\TgModuleRegistrar
{
    public array $calls = [];

    public function processor(string $dtoClass, string $processorClass): self
    {
        $this->calls[] = [__FUNCTION__, $dtoClass, $processorClass];

        return $this;
    }

    public function validationRule(string $ruleClass, int $weight = 0): self
    {
        $this->calls[] = [__FUNCTION__, $ruleClass, $weight];

        return $this;
    }

    public function outboundMiddleware(string $middlewareClass): self
    {
        $this->calls[] = [__FUNCTION__, $middlewareClass];

        return $this;
    }

    public function command(string $name, string $processorClass): self
    {
        $this->calls[] = [__FUNCTION__, $name, $processorClass];

        return $this;
    }

    public function registerAttributed(string $providerClass): self
    {
        $this->calls[] = [__FUNCTION__, $providerClass];

        return $this;
    }

    public function webUi(string $uiClass): self
    {
        $this->calls[] = [__FUNCTION__, $uiClass];

        return $this;
    }

    public function webApi(string $handlerClass): self
    {
        $this->calls[] = [__FUNCTION__, $handlerClass];

        return $this;
    }

    public function webResource(string $providerClass): self
    {
        $this->calls[] = [__FUNCTION__, $providerClass];

        return $this;
    }

    public function webPermissions(string $resolverClass): self
    {
        $this->calls[] = [__FUNCTION__, $resolverClass];

        return $this;
    }
}

final class ScopedFixtureUi
{
}
final class ScopedFixtureApi
{
}
final class ScopedFixtureResource
{
}
final class ScopedFixturePermissions
{
}
