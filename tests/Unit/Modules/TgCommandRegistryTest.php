<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;

final class ModulesCommandFixtureProcessor implements BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract
{
    public static function build(BAGArt\TelegramBot\Processing\BotProcessorContext $context): self
    {
        return new self();
    }

    public function process(
        BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
        BAGArt\TelegramBot\Configs\TgBotConfig $botConfig,
        ?string $action = null,
    ): void {
    }

    public function support(
        BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
        BAGArt\TelegramBot\Configs\TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return true;
    }

    public function isStrictOrdered(
        BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract $dto,
        BAGArt\TelegramBot\Configs\TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }
}

it('registers commands with and without the leading slash', function () {
    $registry = new TgCommandRegistry();
    $registrar = new TypedModuleRegistrar(
        new TypeDTOProcessorRegistry(),
        null,
        null,
        $registry,
    );

    $registrar->command('example_ping', ModulesCommandFixtureProcessor::class);
    $registrar->command('/other', ModulesCommandFixtureProcessor::class);

    expect($registry->has('example_ping'))->toBeTrue();
    expect($registry->processorOf('/example_ping'))->toBe(ModulesCommandFixtureProcessor::class);
    expect($registry->processorOf('other'))->toBe(ModulesCommandFixtureProcessor::class);
    expect($registry->commands())->toHaveCount(2);
});

it('throws LogicException when no command registry is wired', function () {
    (new TypedModuleRegistrar(new TypeDTOProcessorRegistry()))
        ->command('cmd', ModulesCommandFixtureProcessor::class);
})->throws(LogicException::class);

it('parses command names from message texts', function (string $text, ?string $expected) {
    expect(TgCommandRegistry::parseCommandName($text))->toBe($expected);
})->with([
    ['text' => '/cmd arg', 'expected' => 'cmd'],
    ['text' => '/cmd@my_bot arg', 'expected' => 'cmd'],
    ['text' => '/cmd', 'expected' => 'cmd'],
    ['text' => 'plain text', 'expected' => null],
    ['text' => 'no /mid command', 'expected' => null],
    ['text' => '', 'expected' => null],
]);
