<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Modules\CommandRouteContract;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\Enum\ChatPropTypeEnum;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBotExample\ExampleMessageProcessor;
use BAGArt\TelegramBotExample\ExamplePingCommandProcessor;

/**
 * Bot-scoped route overrides (CommandRouteContract) beat the flat command
 * registry; unusable routes fall back to it transparently.
 */
function routeOverrideSelector(?CommandRouteContract $routes): RegisteredUpdateProcessorSelector
{
    $botSetup = app(TgBotSetupFactory::class)->create(serviceConfig: new TgServiceConfig());

    return new RegisteredUpdateProcessorSelector(
        serviceConfig: new TgServiceConfig(),
        botSetup: $botSetup,
        commandRoutes: $routes,
    );
}

function routeMap(array $map): CommandRouteContract
{
    return new class ($map) implements CommandRouteContract {
        public function __construct(private readonly array $map)
        {
        }

        public function processorOf(string $commandName, string $botId): ?string
        {
            return $this->map[$commandName] ?? null;
        }
    };
}

function routeTestUpdate(string $text): UpdateTypeDTO
{
    return new UpdateTypeDTO(
        updateId: 1,
        message: new MessageTypeDTO(
            messageId: 10,
            date: time(),
            chat: new ChatTypeDTO(id: '100', type: ChatPropTypeEnum::GROUP),
            text: $text,
        ),
    );
}

function runRouteUpdate(RegisteredUpdateProcessorSelector $selector, string $text): void
{
    $botConfig = new TgBotConfig(token: 'test:token', botId: 'test_bot');
    $update = routeTestUpdate($text);
    foreach ($selector->selectProcessors($update, $botConfig) as $processors) {
        foreach ($processors as $processor) {
            $processor->process($update->message, $botConfig, 'message');
        }
    }
}

beforeEach(function () {
    config('telegram.modules');
    ExampleMessageProcessor::$receivedTexts = [];
    ExamplePingCommandProcessor::$invokedIn = [];
    ExamplePingCommandProcessor::$replied = [];
});

it('a route override takes precedence over the flat registry', function () {
    // Registry maps example_ping to ExamplePingCommandProcessor; the route
    // reroutes it — the ping processor must never be built or invoked.
    $selector = routeOverrideSelector(routeMap(['example_ping' => ExampleMessageProcessor::class]));

    runRouteUpdate($selector, '/example_ping');

    expect(ExamplePingCommandProcessor::$invokedIn)->toBe([]);
    expect(ExampleMessageProcessor::$receivedTexts)->toBe(['/example_ping']);
});

it('a route whose processor rejects the dto falls back to the regular flow', function () {
    // ExamplePingCommandProcessor::support() only accepts its own command,
    // so the aliased route yields no command processor and the message
    // reaches regular processors untouched.
    $selector = routeOverrideSelector(routeMap(['alias_ping' => ExamplePingCommandProcessor::class]));

    runRouteUpdate($selector, '/alias_ping');

    expect(ExamplePingCommandProcessor::$invokedIn)->toBe([]);
    expect(ExampleMessageProcessor::$receivedTexts)->toBe(['/alias_ping']);
});

it('a route pointing at a missing class falls back to the regular flow', function () {
    $selector = routeOverrideSelector(routeMap(['alias_ping' => '\App\NoSuchProcessor']));

    runRouteUpdate($selector, '/alias_ping');

    expect(ExamplePingCommandProcessor::$invokedIn)->toBe([]);
    expect(ExampleMessageProcessor::$receivedTexts)->toBe(['/alias_ping']);
});

it('an empty route table keeps the flat registry in charge', function () {
    $selector = routeOverrideSelector(routeMap([]));

    runRouteUpdate($selector, '/example_ping');

    expect(ExamplePingCommandProcessor::$invokedIn)->toBe(['100']);
    expect(ExampleMessageProcessor::$receivedTexts)->toBe([]);
});
