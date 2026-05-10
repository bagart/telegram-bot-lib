<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/Support/TestMessageCollectorProcessor.php';
require_once __DIR__ . '/Support/TestTypeDTOCollectorProcessor.php';

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Tests\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

const TOKEN = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

$logger = new TgBotLogWrapper(new Logger('test', [new StreamHandler('php://stdout', Level::Debug)]));
echo "Logger created\n";

$registry = TypeDTOProcessorRegistry::build();
$msgCollector = new TestMessageCollectorProcessor();
$registry->register(MessageTypeDTO::class, $msgCollector);

$updateInit = new UpdateDTOInitProcessor(
    processorRegistry: $registry,
    dispatcherRegistry: new \BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry(),
    logger: $logger,
);
$registry->register(UpdateTypeDTO::class, $updateInit);

echo "Registry configured\n";

// Check what processors are registered for each type
echo "\nProcessors for UpdateTypeDTO:\n";
foreach ($registry->get(UpdateTypeDTO::class) as $p) {
    echo "  - " . $p::class . "\n";
}

echo "\nProcessors for MessageTypeDTO:\n";
foreach ($registry->get(MessageTypeDTO::class) as $p) {
    echo "  - " . $p::class . "\n";
}

// Now run the actual webhook
$secretService = new AutoSecretByTokenService();
$secret = $secretService->secret(TOKEN);

$webhook = new TgWebhookRequestParser(
    tgApiDTOMapper: TgPureFactory::dtoMapper(),
    processorRegistry: $registry,
    secretService: $secretService,
    logger: $logger,
);

$payload = [
    'update_id' => 987654321,
    'message' => [
        'message_id' => 42,
        'from' => ['id' => 111222333, 'is_bot' => false, 'first_name' => 'John'],
        'chat' => ['id' => 111222333, 'first_name' => 'John', 'type' => 'private'],
        'date' => 1700000000,
        'text' => 'Hello, bot!',
    ],
];

echo "\n=== Running parse ===\n";
$result = $webhook->parse($payload, $secret);
echo "Parse result: " . ($result ? 'true' : 'false') . "\n";

echo "\nMessage collector count: " . $msgCollector->count() . "\n";
echo "Collected items:\n";
foreach ($msgCollector->collected as $item) {
    echo "  - dto: " . $item['dto']::class . ", text: " . ($item['dto']->text ?? 'N/A') . "\n";
}
