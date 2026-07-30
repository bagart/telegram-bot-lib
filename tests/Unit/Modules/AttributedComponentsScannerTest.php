<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Modules\AttributedComponentsScanner;
use BAGArt\TelegramBot\Modules\Attributes\TgCommandAttribute;
use BAGArt\TelegramBot\Modules\ModuleBootloader;
use BAGArt\TelegramBot\Modules\TgModuleRegistry;
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Outbound\OutboundMiddlewareRegistry;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRuleRegistry;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed\AttributedFixtureModule;
use BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed\AttributedHelloCommand;
use BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed\AttributedMessageProcessor;
use BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed\AttributedPassMiddleware;
use BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed\AttributedWeightRule;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use Illuminate\Cache\ArrayStore;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

/**
 * Registrar bundle with fresh registries — one per test scenario.
 */
function attributedRegistrarBundle(): array
{
    $processors = new TypeDTOProcessorRegistry();
    $rules = new MessageValidationRuleRegistry();
    $middlewares = new OutboundMiddlewareRegistry();
    $commands = new TgCommandRegistry();
    $registrar = new TypedModuleRegistrar($processors, $rules, $middlewares, $commands);

    return [$registrar, $processors, $rules, $middlewares, $commands];
}

/**
 * @param  TypeDTOProcessorRegistry  $registry
 * @return array<string, list<string>> class-string processors per DTO
 */
function attributedProcessorClasses(TypeDTOProcessorRegistry $registry): array
{
    $property = new ReflectionProperty(TypeDTOProcessorRegistry::class, 'processors');
    $property->setAccessible(true);

    return array_map(
        static fn (array $list): array => array_map(
            static fn (mixed $p): string => \is_string($p) ? $p : $p::class,
            $list,
        ),
        $property->getValue($registry),
    );
}

it('registers all attribute-declared components equivalently to explicit registrar calls', function () {
    [$attributed, $aProcessors, $aRules, $aMiddlewares, $aCommands] = attributedRegistrarBundle();
    [$explicit, $eProcessors, $eRules, $eMiddlewares, $eCommands] = attributedRegistrarBundle();

    $scanner = new AttributedComponentsScanner();
    $declared = $scanner->scanAndRegister(AttributedFixtureModule::class, $attributed);

    $explicit
        ->processor(MessageTypeDTO::class, AttributedMessageProcessor::class)
        ->validationRule(AttributedWeightRule::class, 7)
        ->outboundMiddleware(AttributedPassMiddleware::class)
        ->command('attributed_hello', AttributedHelloCommand::class);

    expect($declared)->toBe([
        AttributedHelloCommand::class,
        AttributedMessageProcessor::class,
        AttributedPassMiddleware::class,
        AttributedWeightRule::class,
    ]);

    expect(attributedProcessorClasses($aProcessors))->toBe(attributedProcessorClasses($eProcessors));
    expect(attributedProcessorClasses($aProcessors)[MessageTypeDTO::class])
        ->toBe([AttributedMessageProcessor::class]);

    expect(array_map(
        static fn (object $r): string => $r::class,
        iterator_to_array($aRules->rules()),
    ))->toBe(array_map(
        static fn (object $r): string => $r::class,
        iterator_to_array($eRules->rules()),
    ));
    expect($aMiddlewares->classes())->toBe($eMiddlewares->classes());
    expect($aCommands->commands())->toBe($eCommands->commands());
    expect($aCommands->has('attributed_hello'))->toBeTrue();
});

it('replays from cache without rescanning and rescans when a file mtime changes', function () {
    $cache = new ASKCacheWrapper(new ArrayStore());
    [$registrar, , , , ] = attributedRegistrarBundle();

    $scanner = new AttributedComponentsScanner($cache);
    $scanner->scanAndRegister(AttributedFixtureModule::class, $registrar);
    expect($cache->get('tg.mod.attrib.attributed_fixture'))->not->toBeNull();

    // corrupt the cached components — a cache hit must replay the corrupted
    // payload, proving no rescan happened
    $cached = $cache->get('tg.mod.attrib.attributed_fixture');
    $cached['components'] = [['method' => 'command', 'class' => AttributedHelloCommand::class, 'args' => ['spoofed', AttributedHelloCommand::class]]];
    $cache->forever('tg.mod.attrib.attributed_fixture', $cached);

    [$registrar2, , , , $commands2] = attributedRegistrarBundle();
    $scanner->scanAndRegister(AttributedFixtureModule::class, $registrar2);
    expect($commands2->commands())->toBe(['spoofed' => AttributedHelloCommand::class]);

    // mtime change invalidates the cache entry and forces a rescan
    $providerFile = (new ReflectionClass(AttributedFixtureModule::class))->getFileName();
    expect(touch($providerFile))->toBeTrue();

    [$registrar3, , , , $commands3] = attributedRegistrarBundle();
    $scanner->scanAndRegister(AttributedFixtureModule::class, $registrar3);
    expect($commands3->commands())->toBe(['attributed_hello' => AttributedHelloCommand::class]);
});

it('never picks up attributed classes outside the module source directory', function () {
    // carries the attribute but lives in the test file, not in the fixture dir
    new #[TgCommandAttribute(name: 'outsider_command')] class () {};

    [$registrar, , , , $commands] = attributedRegistrarBundle();
    (new AttributedComponentsScanner())->scanAndRegister(AttributedFixtureModule::class, $registrar);

    expect($commands->has('outsider_command'))->toBeFalse();
    expect($commands->commands())->toBe(['attributed_hello' => AttributedHelloCommand::class]);
});

it('skips a module whose attributed class lacks the contract (fault isolation)', function () {
    $commands = new TgCommandRegistry();
    $registrar = new TypedModuleRegistrar(
        new TypeDTOProcessorRegistry(),
        new MessageValidationRuleRegistry(),
        new OutboundMiddlewareRegistry(),
        $commands,
        new AttributedComponentsScanner(),
    );
    $bootloader = new ModuleBootloader(
        registrar: $registrar,
        registry: new TgModuleRegistry(),
        logger: new ASKLogWrapper(new Logger('test', [new NullHandler()])),
    );

    $booted = $bootloader->bootAll([
        \BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\AttributedBroken\AttributedBrokenModule::class,
        AttributedFixtureModule::class,
    ]);

    expect($booted)->toBe(['attributed_fixture']);
    expect($commands->has('broken_command'))->toBeFalse();
    expect($commands->has('attributed_hello'))->toBeTrue();
});

it('throws LogicException when no scanner is wired into the registrar', function () {
    [$registrar] = attributedRegistrarBundle();
    $registrar->registerAttributed(AttributedFixtureModule::class);
})->throws(LogicException::class);
