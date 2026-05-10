<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/Support/TestMessageCollectorProcessor.php';
require_once __DIR__ . '/Support/TestTypeDTOCollectorProcessor.php';

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Tests\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

const TOKEN = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

$logger = new TgBotLogWrapper(new Logger('test', [new StreamHandler('php://stdout', Level::Info)]));

$registry = TypeDTOProcessorRegistry::build();
$msgCollector = new TestMessageCollectorProcessor();
$registry->register(MessageTypeDTO::class, $msgCollector);

$updateInit = new UpdateDTOInitProcessor(
    processorRegistry: $registry,
    dispatcherRegistry: new \BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry(),
    logger: $logger,
);

// Manually create the update DTO
$mapper = new TgApiDTOMapper(
    \BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry::build(TgApiEntityScopeEnum::class, $logger),
    $logger
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

$updateDTO = $mapper->fromArray(UpdateTypeDTO::class, $payload);

// Now manually simulate what UpdateDTOInitProcessor::process() does
echo "=== Simulating processDto manually ===\n";

$dto = $updateDTO->message;
echo "DTO class: " . $dto::class . "\n";
echo "Is TgApiTypeDTOContract: " . ($dto instanceof TgApiTypeDTOContract ? 'yes' : 'no') . "\n";

// Simulate processDto for MessageTypeDTO
echo "\n=== registry->get(MessageTypeDTO::class) ===\n";
foreach ($registry->get(MessageTypeDTO::class, new TgUpdateConfig(TOKEN)) as $processor) {
    echo "Processor: " . $processor::class . "\n";
    echo "  support(dto, null): " . ($processor->support($dto, null) ? 'yes' : 'no') . "\n";
    if ($processor->support($dto, null)) {
        echo "  Calling process()...\n";
        $processor->process($dto, '123456789', null);
        echo "  process() returned\n";
    }
}

echo "\nMessage collector count: " . $msgCollector->count() . "\n";
foreach ($msgCollector->collected as $item) {
    echo "  Collected: " . $item['dto']::class . " text=" . $item['dto']->text . "\n";
}

// Now test with the actual process() method
echo "\n=== Testing with actual UpdateDTOInitProcessor::process() ===\n";
$registry2 = TypeDTOProcessorRegistry::build();
$msgCollector2 = new TestMessageCollectorProcessor();
$registry2->register(MessageTypeDTO::class, $msgCollector2);

$updateInit2 = new UpdateDTOInitProcessor(
    processorRegistry: $registry2,
    dispatcherRegistry: new \BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry(),
    logger: new TgBotLogWrapper(new Logger('test2')),
);

$updateDTO2 = $mapper->fromArray(UpdateTypeDTO::class, $payload);
$updateInit2->process($updateDTO2, '123456789');

echo "Message collector count after process(): " . $msgCollector2->count() . "\n";
foreach ($msgCollector2->collected as $item) {
    echo "  Collected: " . $item['dto']::class . " text=" . $item['dto']->text . "\n";
}
