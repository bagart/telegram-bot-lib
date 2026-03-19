<?php

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/Support/TestMessageCollectorProcessor.php';
require_once __DIR__.'/Support/TestTypeDTOCollectorProcessor.php';

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Tests\Integration\Support\TestMessageCollectorProcessor;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgBotSetupFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

const TOKEN = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

$logger = new ASKLogWrapper(new Logger('test', [new StreamHandler('php://stdout', Level::Info)]));

$registry = TypeDTOProcessorRegistry::build();
$msgCollector = new TestMessageCollectorProcessor();
$registry->register(MessageTypeDTO::class, $msgCollector);

$factory = TgBotSetupFactory::build();
$setup = $factory->create(
    serviceConfig: new TgServiceConfig(),
    processorRegistryOverride: $registry,
);
$selector = new RegisteredUpdateProcessorSelector(
    serviceConfig: new TgServiceConfig(),
    botSetup: $setup,
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

echo "=== Testing selectProcessors() ===\n";
$botConfig = new \BAGArt\TelegramBot\Configs\TgBotConfig(token: TOKEN);
foreach ($selector->selectProcessors($updateDTO, $botConfig) as $property => $processors) {
    $isStrictOrdered = $processor->isStrictOrdered(
        dto: $updateDTO,
        botConfig: $botConfig,
        action: $property,
    );
    echo "  -> $property: ".$processor::class.($isStrictOrdered ? ' (strict)' : '')."\n";
}

echo "\nMessage collector count: ".$msgCollector->count()."\n";
foreach ($msgCollector->collected as $item) {
    echo "  Collected: ".$item['dto']::class." text=".$item['dto']->text."\n";
}
