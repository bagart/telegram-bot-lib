<?php

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/Support/TestMessageCollectorProcessor.php';
require_once __DIR__.'/Support/TestTypeDTOCollectorProcessor.php';

use BAGArt\TelegramBot\Tests\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
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

// Use reflection to call processDto directly
echo "=== Using reflection to call processDto ===\n";

$reflection = new ReflectionClass($updateInit);
$processDtoMethod = $reflection->getMethod('processDto');
$processDtoMethod->setAccessible(true);

// Call processDto directly
$processDtoMethod->invoke($updateInit, $updateDTO->message, '123456789', 'message');

echo "Message collector count: ".$msgCollector->count()."\n";
foreach ($msgCollector->collected as $item) {
    echo "  Collected: ".$item['dto']::class." text=".$item['dto']->text."\n";
}

// Also check the config
echo "\n=== Checking UpdateDTOInitProcessor internals ===\n";
$configProp = $reflection->getProperty('config');
$configProp->setAccessible(true);
$config = $configProp->getValue($updateInit);
echo "Config: ".($config === null ? 'null' : $config::class)."\n";
if ($config) {
    $dispProp = new ReflectionProperty($config, 'dispatcher');
    echo "Config dispatcher: ".$dispProp->getValue($config)."\n";
}

$registryProp = $reflection->getProperty('processorRegistry');
$registryProp->setAccessible(true);
$reg = $registryProp->getValue($updateInit);
echo "Processor registry: ".($reg === null ? 'null' : $reg::class)."\n";

$cachedProp = $reflection->getProperty('cachedProcessors');
$cachedProp->setAccessible(true);
$cached = $cachedProp->getValue($updateInit);
echo "Cached processors: ".json_encode($cached)."\n";
