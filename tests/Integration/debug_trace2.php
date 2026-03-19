<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__.'/Support/TestMessageCollectorProcessor.php';
require_once __DIR__.'/Support/TestTypeDTOCollectorProcessor.php';

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
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

$logger = new ASKLogWrapper(new Logger('test', [new StreamHandler('php://stdout', Level::Debug)]));

$registry = TypeDTOProcessorRegistry::build();
$msgCollector = new TestMessageCollectorProcessor();
$registry->register(MessageTypeDTO::class, $msgCollector);

$serviceConfig = new TgServiceConfig();

$factory = TgBotSetupFactory::build();
$setup = $factory->create(
    serviceConfig: $serviceConfig,
    processorRegistryOverride: $registry,
);
$selector = new RegisteredUpdateProcessorSelector(
    serviceConfig: $serviceConfig,
    botSetup: $setup,
);

echo "=== Registry contents ===\n";
echo "Processors for UpdateTypeDTO:\n";
$count = 0;
foreach ($registry->get(UpdateTypeDTO::class, $serviceConfig) as $p) {
    $count++;
    echo "  [$count] " . $p::class . "\n";
    echo "      support(UpdateTypeDTO): " . ($p->support(new UpdateTypeDTO(0), new TgBotConfig(token: TOKEN)) ? 'yes' : 'no') . "\n";
}

echo "\nProcessors for MessageTypeDTO:\n";
$count = 0;
foreach ($registry->get(MessageTypeDTO::class, $serviceConfig) as $p) {
    $count++;
    echo "  [$count] " . $p::class . "\n";
}

// Now manually test the flow
echo "\n=== Manual flow test ===\n";

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

$mapper = new TgApiDTOMapper(
    \BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry::build(TgApiEntityScopeEnum::class, $logger),
    $logger
);

$updateDTO = $mapper->fromArray(UpdateTypeDTO::class, $payload);
echo "UpdateDTO class: " . $updateDTO::class . "\n";
echo "Message class: " . ($updateDTO->message ? $updateDTO->message::class : "null") . "\n";
echo "Message instanceof TgApiTypeDTOContract: " . ($updateDTO->message instanceof TgApiTypeDTOContract ? 'yes' : 'no') . "\n";

echo "\n=== Testing selectProcessors() ===\n";
$botConfig = new TgBotConfig(token: TOKEN);
foreach ($selector->selectProcessors($updateDTO, $botConfig) as $property => $processors) {
    $isStrictOrdered = $processor->isStrictOrdered(
        dto: $updateDTO,
        botConfig: $botConfig,
        action: $property,
    );
    echo "  -> $property: ".$processor::class.($isStrictOrdered ? ' (strict)' : '')."\n";
}

echo "\nMessage collector count: " . $msgCollector->count() . "\n";
foreach ($msgCollector->collected as $item) {
    echo "  Collected: " . $item['dto']::class . " text=" . $item['dto']->text . "\n";
}
