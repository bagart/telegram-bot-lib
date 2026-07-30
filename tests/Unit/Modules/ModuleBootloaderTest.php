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
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\Processing\Processors\CallableProcessor;
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
                $registrar->processor(MessageTypeDTO::class, CallableProcessor::class);
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
        expect($built[0])->toBeInstanceOf(CallableProcessor::class);
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
                $registrar->processor(MessageTypeDTO::class, CallableProcessor::class);
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

    it('rejects providers that do not implement TgModuleContract', function () {
        $bootloader = new ModuleBootloader(
            registrar: modulesTestRegistrar(TypeDTOProcessorRegistry::build()),
            registry: new TgModuleRegistry(),
            logger: modulesTestLogger(),
        );

        expect($bootloader->bootOne(DateTime::class))->toBeNull();
    });
});
