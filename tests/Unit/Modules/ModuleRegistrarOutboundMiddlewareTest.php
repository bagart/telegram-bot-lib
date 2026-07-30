<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Modules\ModuleBootloader;
use BAGArt\TelegramBot\Modules\TgModuleContract;
use BAGArt\TelegramBot\Modules\TgModuleDescriptor;
use BAGArt\TelegramBot\Modules\TgModuleRegistrar;
use BAGArt\TelegramBot\Modules\TgModuleRegistry;
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundMiddlewareRegistry;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundSkipException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

final class ModulesOutboundPassMiddleware implements OutboundMiddleware
{
    public static int $calls = 0;

    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        self::$calls++;
        $next($envelope);
    }
}

final class ModulesOutboundDropMiddleware implements OutboundMiddleware
{
    public function handle(OutboundEnvelope $envelope, Closure $next): void
    {
        throw new OutboundSkipException(reason: 'dropped by fixture');
    }
}

it('registers an outbound middleware class into the registry (dedup)', function () {
    $registry = new OutboundMiddlewareRegistry();
    $registrar = new TypedModuleRegistrar(new TypeDTOProcessorRegistry(), null, $registry);

    $registrar->outboundMiddleware(ModulesOutboundPassMiddleware::class);
    $registrar->outboundMiddleware(ModulesOutboundPassMiddleware::class);

    expect($registry->classes())->toBe([ModulesOutboundPassMiddleware::class]);
    expect($registry->middlewares())->toHaveCount(1);
    expect($registry->middlewares()[0])->toBeInstanceOf(ModulesOutboundPassMiddleware::class);
    // instances are cached — same object on the second call
    expect($registry->middlewares()[0])->toBe($registry->middlewares()[0]);
});

it('throws LogicException when no outbound middleware registry is wired', function () {
    (new TypedModuleRegistrar(new TypeDTOProcessorRegistry()))
        ->outboundMiddleware(ModulesOutboundPassMiddleware::class);
})->throws(LogicException::class);

it('boots a module with middleware and skips a module with an invalid middleware class', function () {
    $registry = new OutboundMiddlewareRegistry();
    $bootloader = new ModuleBootloader(
        registrar: new TypedModuleRegistrar(new TypeDTOProcessorRegistry(), null, $registry),
        registry: new TgModuleRegistry(),
        logger: new ASKLogWrapper(new Logger('test', [new NullHandler()])),
    );

    $valid = new class () implements TgModuleContract {
        public static function descriptor(): TgModuleDescriptor
        {
            return new TgModuleDescriptor(id: 'outbound_valid_fixture', name: 'ValidFixture', version: '1.0.0');
        }

        public static function register(TgModuleRegistrar $registrar): void
        {
            $registrar->outboundMiddleware(ModulesOutboundPassMiddleware::class);
        }
    };

    $invalid = new class () implements TgModuleContract {
        public static function descriptor(): TgModuleDescriptor
        {
            return new TgModuleDescriptor(id: 'outbound_invalid_fixture', name: 'InvalidFixture', version: '1.0.0');
        }

        public static function register(TgModuleRegistrar $registrar): void
        {
            $registrar->outboundMiddleware('NonExistentMiddlewareClass');
        }
    };

    $booted = $bootloader->bootAll([$valid::class, $invalid::class]);

    expect($booted)->toBe(['outbound_valid_fixture']);
    expect($registry->classes())->toBe([ModulesOutboundPassMiddleware::class]);
});

it('registry middleware runs in the pipeline before the executor and can drop an envelope', function () {
    $registry = new OutboundMiddlewareRegistry();
    $registry->registerClass(ModulesOutboundDropMiddleware::class);

    $pipeline = new OutboundPipeline([
        ...$registry->middlewares(),
        new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new RuntimeException('Executor must not run for dropped envelope');
            }
        },
    ]);

    $envelope = new OutboundEnvelope(
        task: new OutboundTask(
            id: 'test-task',
            botConfig: new TgBotConfig(token: 't:token', botId: 'test_bot'),
            dtoClass: 'SomeDto',
            dtoData: ['text' => 'blocked payload'],
        ),
        state: new OutboundTaskState(),
    );

    $pipeline->execute($envelope);
})->throws(OutboundSkipException::class);
