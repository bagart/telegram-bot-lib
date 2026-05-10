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
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

const TOKEN = '123456789:ABCdefGHIjklMNOpqrsTUVwxyz';

$logger = new TgBotLogWrapper(new Logger('test', [new StreamHandler('php://stdout', Level::Debug)]));

$registry = TypeDTOProcessorRegistry::build();
$msgCollector = new TestMessageCollectorProcessor();
$registry->register(MessageTypeDTO::class, $msgCollector);

$updateInit = new UpdateDTOInitProcessor(
    processorRegistry: $registry,
    dispatcherRegistry: new \BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry(),
    logger: $logger,
);
$registry->register(UpdateTypeDTO::class, $updateInit);

echo "=== Registry contents ===\n";
echo "Processors for UpdateTypeDTO:\n";
$count = 0;
foreach ($registry->get(UpdateTypeDTO::class) as $p) {
    $count++;
    echo "  [$count] " . $p::class . "\n";
    echo "      support(UpdateTypeDTO): " . ($p->support(new UpdateTypeDTO(0)) ? 'yes' : 'no') . "\n";
}

echo "\nProcessors for MessageTypeDTO:\n";
$count = 0;
foreach ($registry->get(MessageTypeDTO::class) as $p) {
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

echo "\n=== Testing UpdateDTOInitProcessor::process() ===\n";
foreach ($updateDTO::tgPropertyMetas() as $meta) {
    echo "  Meta: {$meta->property} (tg: {$meta->tgPropName}) => " . implode(', ', $meta->types) . "\n";
    if (property_exists($updateDTO, $meta->property)) {
        $value = $updateDTO->{$meta->property};
        echo "    value: " . ($value === null ? 'null' : (is_object($value) ? $value::class : gettype($value))) . "\n";
    }
}

echo "\n=== Calling process() ===\n";
$updateInit->process($updateDTO, '123456789');

echo "Message collector count after process(): " . $msgCollector->count() . "\n";
foreach ($msgCollector->collected as $item) {
    echo "  Collected: " . $item['dto']::class . " text=" . $item['dto']->text . "\n";
}
