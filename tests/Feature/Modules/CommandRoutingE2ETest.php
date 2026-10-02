<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Modules\ModuleEnablementContract;
use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\Enum\ChatPropTypeEnum;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBotManagement\Models\TgBot;
use BAGArt\TelegramBotManagement\Models\TgModuleEnablement;
use BAGArt\TelegramBotExample\ExampleMessageProcessor;
use BAGArt\TelegramBotExample\ExamplePingCommandProcessor;

beforeEach(function () {
    config('telegram.modules'); // forces the module config scan
    ExampleMessageProcessor::$receivedTexts = [];
    ExamplePingCommandProcessor::$invokedIn = [];
    ExamplePingCommandProcessor::$replied = [];
});

function commandTestUpdate(string $text, int $chatId = 100): UpdateTypeDTO
{
    return new UpdateTypeDTO(
        updateId: 1,
        message: new MessageTypeDTO(
            messageId: 10,
            date: time(),
            chat: new ChatTypeDTO(id: (string) $chatId, type: ChatPropTypeEnum::GROUP),
            text: $text,
        ),
    );
}

function commandTestSelector(): RegisteredUpdateProcessorSelector
{
    $factory = app(TgBotSetupFactory::class);
    $botSetup = $factory->create(serviceConfig: new TgServiceConfig());

    return new RegisteredUpdateProcessorSelector(
        serviceConfig: new TgServiceConfig(),
        botSetup: $botSetup,
    );
}

function commandEnablementSelector(): RegisteredUpdateProcessorSelector
{
    $factory = app(TgBotSetupFactory::class);

    return new RegisteredUpdateProcessorSelector(
        serviceConfig: new TgServiceConfig(),
        botSetup: $factory->create(serviceConfig: new TgServiceConfig()),
        moduleEnablement: app(ModuleEnablementContract::class),
    );
}

function runCommandFlow(RegisteredUpdateProcessorSelector $selector, UpdateTypeDTO $update): void
{
    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');

    foreach ($selector->selectProcessors($update, $botConfig) as $processors) {
        foreach ($processors as $processor) {
            $processor->process($update->message, $botConfig, 'message');
        }
    }
}

it('Example module registers the /example_ping command', function () {
    expect(app(TgCommandRegistry::class)->processorOf('example_ping'))
        ->toBe(ExamplePingCommandProcessor::class);
});

it('routes /example_ping exclusively to the command processor (webhook shape, e2e)', function () {
    $selector = commandTestSelector();
    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');
    $update = commandTestUpdate('/example_ping');

    $ran = [];
    foreach ($selector->selectProcessors($update, $botConfig) as $processors) {
        foreach ($processors as $processor) {
            $ran[] = $processor::class;
            $processor->process($update->message, $botConfig, 'message');
        }
    }

    // the command intercepted the update: no regular message processors ran
    expect($ran)->toBe([ExamplePingCommandProcessor::class]);
    expect(ExamplePingCommandProcessor::$invokedIn)->toBe(['100']);
    expect(ExampleMessageProcessor::$receivedTexts)->toBe([]);
});

it('a non-command message still reaches regular processors', function () {
    $selector = commandTestSelector();
    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');
    $update = commandTestUpdate('hello module');

    foreach ($selector->selectProcessors($update, $botConfig) as $processors) {
        foreach ($processors as $processor) {
            $processor->process($update->message, $botConfig, 'message');
        }
    }

    expect(ExampleMessageProcessor::$receivedTexts)->toBe(['hello module']);
    expect(ExamplePingCommandProcessor::$invokedIn)->toBe([]);
});

it('an unknown command falls back to the regular message flow', function () {
    $selector = commandTestSelector();
    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');
    $update = commandTestUpdate('/unknown_command');

    foreach ($selector->selectProcessors($update, $botConfig) as $processors) {
        foreach ($processors as $processor) {
            $processor->process($update->message, $botConfig, 'message');
        }
    }

    expect(ExamplePingCommandProcessor::$invokedIn)->toBe([]);
    // regular processors still observed the message
    expect(ExampleMessageProcessor::$receivedTexts)->toBe(['/unknown_command']);
});

it('answers /example_ping with the module_settings-configured reply text', function () {
    TgBot::create(['bot_id' => 'test_bot', 'token' => 'test:token']);
    TgModuleEnablement::factory()
        ->forChat('test_bot', 100)
        ->create([
            'module_id' => 'example',
            'module_settings' => ['ping_reply' => 'pong-custom'],
        ]);

    $selector = commandTestSelector();
    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');
    $update = commandTestUpdate('/example_ping');

    foreach ($selector->selectProcessors($update, $botConfig) as $processors) {
        foreach ($processors as $processor) {
            $processor->process($update->message, $botConfig, 'message');
        }
    }

    expect(ExamplePingCommandProcessor::$invokedIn)->toBe(['100']);
    expect(ExamplePingCommandProcessor::$replied)->toBe(['pong-custom']);
});

it('dispatches the command on bot scope: chat-disabled module keeps its command (Q11-D1)', function () {
    TgBot::create(['bot_id' => 'test_bot', 'token' => 'test:token']);
    TgModuleEnablement::factory()
        ->forBot('test_bot')
        ->enabled(true)
        ->create(['module_id' => 'example']);
    TgModuleEnablement::factory()
        ->forChat('test_bot', 100)
        ->enabled(false)
        ->create(['module_id' => 'example']);

    $selector = commandEnablementSelector();

    runCommandFlow($selector, commandTestUpdate('/example_ping'));

    // Q11-D1: commands dispatch on bot scope — the chat-level row does not gate them
    expect(ExamplePingCommandProcessor::$invokedIn)->toBe(['100']);

    // the regular processor stays chat-gated in the same chat...
    runCommandFlow($selector, commandTestUpdate('hello module'));
    expect(ExampleMessageProcessor::$receivedTexts)->toBe([]);

    // ...while other chats keep the module
    runCommandFlow($selector, commandTestUpdate('hello module', 200));
    expect(ExampleMessageProcessor::$receivedTexts)->toBe(['hello module']);
});

it('still suppresses the command when the module is disabled at bot scope (Q11-D1)', function () {
    TgBot::create(['bot_id' => 'test_bot', 'token' => 'test:token']);
    TgModuleEnablement::factory()
        ->forBot('test_bot')
        ->enabled(false)
        ->create(['module_id' => 'example']);

    $selector = commandEnablementSelector();

    runCommandFlow($selector, commandTestUpdate('/example_ping'));

    // bot-scope disable suppresses the command (and the fall-through regular flow too)
    expect(ExamplePingCommandProcessor::$invokedIn)->toBe([]);
    expect(ExampleMessageProcessor::$receivedTexts)->toBe([]);

    runCommandFlow($selector, commandTestUpdate('hello module'));
    expect(ExampleMessageProcessor::$receivedTexts)->toBe([]);
});
