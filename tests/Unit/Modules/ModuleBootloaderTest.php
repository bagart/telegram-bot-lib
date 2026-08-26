<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Modules\ModuleBootloader;
use BAGArt\TelegramBot\Modules\TgModuleCapability;
use BAGArt\TelegramBot\Modules\TgModuleContract;
use BAGArt\TelegramBot\Modules\TgModuleDescriptor;
use BAGArt\TelegramBot\Modules\TgModuleRegistrar;
use BAGArt\TelegramBot\Modules\TgModuleRegistry;
use BAGArt\TelegramBot\Modules\TgWebPermissionRegistry;
use BAGArt\TelegramBot\Modules\TgWebUiRegistry;
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\Processing\Processors\DbgDTOToLoggerProcessor;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

function modulesTestLogger(): ASKLogWrapper
{
    return new ASKLogWrapper(new Logger('test', [new NullHandler()]));
}

function modulesTestRegistrar(TypeDTOProcessorRegistry $processorRegistry): TgModuleRegistrar
{
    return new TypedModuleRegistrar($processorRegistry);
}

final class ScopedUiManifestFixture
{
}
final class ScopedPermissionsFixture
{
}

function modulesTestContext(): BotProcessorContext
{
    $botSetup = TgBotSetupFactory::build()->create(serviceConfig: new TgServiceConfig());

    return BotProcessorContext::fromBotSetup($botSetup);
}

describe('ModuleBootloader', function () {
    it('boots a module and registers its processor', function () {
        $processorRegistry = TypeDTOProcessorRegistry::build();
        $registry = new TgModuleRegistry();
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar($processorRegistry),
            registry: $registry,
            logger: modulesTestLogger(),
        );

        $provider = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(
                    id: 'example',
                    name: 'Example',
                    version: '1.0.0',
                    capabilities: [TgModuleCapability::Processor],
                );
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
                $registrar->processor(MessageTypeDTO::class, DbgDTOToLoggerProcessor::class);
            }
        });

        $booted = $bootloader->bootAll([$provider]);

        expect($booted)->toBe(['example']);
        expect($registry->has('example'))->toBeTrue();
        expect($registry->get('example')->name)->toBe('Example');
        expect($registry->defaultEnabledOf('example'))->toBeTrue();

        $built = iterator_to_array(
            $processorRegistry->get(MessageTypeDTO::class, modulesTestContext())
        );
        expect($built)->toHaveCount(1);
        expect($built[0])->toBeInstanceOf(DbgDTOToLoggerProcessor::class);
    });

    it('is idempotent: repeated boot does not duplicate', function () {
        $processorRegistry = TypeDTOProcessorRegistry::build();
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar($processorRegistry),
            registry: new TgModuleRegistry(),
            logger: modulesTestLogger(),
        );

        $provider = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(id: 'dup', name: 'Dup', version: '1.0.0');
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
                $registrar->processor(MessageTypeDTO::class, DbgDTOToLoggerProcessor::class);
            }
        });

        $bootloader->bootAll([$provider]);
        $bootloader->bootAll([$provider]);

        $built = iterator_to_array(
            $processorRegistry->get(MessageTypeDTO::class, modulesTestContext())
        );
        expect($built)->toHaveCount(1);
    });

    it('skips a broken module but boots the rest (fault isolation)', function () {
        $processorRegistry = TypeDTOProcessorRegistry::build();
        $registry = new TgModuleRegistry();
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar($processorRegistry),
            registry: $registry,
            logger: modulesTestLogger(),
        );

        $broken = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                throw new RuntimeException('broken descriptor');
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
            }
        });

        $working = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(id: 'working', name: 'Working', version: '1.0.0');
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
            }
        });

        $booted = $bootloader->bootAll([$broken, $working]);

        expect($booted)->toBe(['working']);
        expect($registry->has('working'))->toBeTrue();
        expect($registry->moduleIds())->toBe(['working']);
    });

    it('skips duplicate module ids: first registered wins', function () {
        $makeProvider = fn () => get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(id: 'same-id', name: 'static', version: '1.0.0');
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
            }
        });

        $registry = new TgModuleRegistry();
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar(TypeDTOProcessorRegistry::build()),
            registry: $registry,
            logger: modulesTestLogger(),
        );

        $booted = $bootloader->bootAll([$makeProvider(), $makeProvider()]);

        expect($booted)->toBe(['same-id']);
        expect($registry->all())->toHaveCount(1);
    });

    it('passes a module-scoped registrar and records (ownerId, class) web pairs', function () {
        $uiRegistry = new TgWebUiRegistry();
        $permissionRegistry = new TgWebPermissionRegistry();
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar(TypeDTOProcessorRegistry::build()),
            registry: new TgModuleRegistry(),
            logger: modulesTestLogger(),
            webUiRegistry: $uiRegistry,
            webPermissionRegistry: $permissionRegistry,
        );

        $provider = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(
                    id: 'scoped-ui',
                    name: 'Scoped UI',
                    version: '1.0.0',
                    capabilities: [TgModuleCapability::Ui],
                );
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
                $registrar->webUi(ScopedUiManifestFixture::class);
                $registrar->webPermissions(ScopedPermissionsFixture::class);
            }
        });

        expect($bootloader->bootAll([$provider]))->toBe(['scoped-ui']);
        expect($uiRegistry->all())->toBe([['module' => 'scoped-ui', 'class' => ScopedUiManifestFixture::class]]);
        expect($permissionRegistry->all())->toBe([['module' => 'scoped-ui', 'class' => ScopedPermissionsFixture::class]]);
    });

    it('records failed modules in failed() but keeps their descriptors registered', function () {
        $registry = new TgModuleRegistry();
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar(TypeDTOProcessorRegistry::build()),
            registry: $registry,
            logger: modulesTestLogger(),
        );

        $broken = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(id: 'broken-register', name: 'Broken', version: '1.0.0');
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
                throw new RuntimeException('register exploded');
            }
        });
        $working = get_class(new class () implements TgModuleContract {
            public static function descriptor(): TgModuleDescriptor
            {
                return new TgModuleDescriptor(id: 'healthy', name: 'Healthy', version: '1.0.0');
            }

            public static function register(TgModuleRegistrar $registrar): void
            {
            }
        });

        $booted = $bootloader->bootAll([$broken, $working]);

        expect($booted)->toBe(['healthy']);
        expect($bootloader->failed())->toBe(['broken-register']);
        expect($registry->has('broken-register'))->toBeTrue();
        expect($registry->has('healthy'))->toBeTrue();
    });

    it('rejects providers that do not implement TgModuleContract', function () {
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar(TypeDTOProcessorRegistry::build()),
            registry: new TgModuleRegistry(),
            logger: modulesTestLogger(),
        );

        expect($bootloader->bootOne(DateTime::class))->toBeNull();
    });
});
