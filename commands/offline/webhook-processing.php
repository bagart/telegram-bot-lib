<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/../includes/examples/webhook-payloads.php';
require_once __DIR__.'/../includes/validate-options.php';
require_once __DIR__.'/../includes/resolve-token.php';

$options = parseCommandOptions([
    'token::',
    'echo',
    'store',
    'log',
    'help',
]);

if (isset($options['help'])) {
    echo "Usage:

php commands/offline/webhook-processing.php      # process webhook payloads with DTOProcessor
  --echo                                         # echo reply to messages
  --store                                        # store messages to database
  --log                                          # log messages to stderr
  --token=xxx:xxx                                # use custom token
";
    exit(0);
}

$token = getCommandToken($options);
$botId = explode(':', $token)[0];

// --- Setup ---
$echo = array_key_exists('echo', $options);
$store = array_key_exists('store', $options);
$log = array_key_exists('log', $options);

$logger = TgPureFactory::logger();
$config = new TgUpdateExampleConfig(
    token: $token,
    dispatcher: TgPureFactory::syncDispatcherType(),
);

$processor = new UpdateDTOInitProcessor(
    processorRegistry: TgPureFactory::processorRegistry(
        processors: ['echo' => $echo, 'log' => $log, 'store' => $store],
        config: $config,
    ),
    dispatcherRegistry: PipelineDispatcherRegistry::build(),
    logger: $logger,
);

echo "=== Webhook Processing Mode ===\n";
$flags = implode(' ', array_filter([
    $echo ? '[ECHO]' : null,
    $store ? '[STORE]' : null,
    $log ? '[LOG]' : null,
]));
echo "Flags: {$flags}\n\n";

$mapper = TgPureFactory::dtoMapper();

foreach (getWebhookPayloads() as $updateRaw) {
    /** @var TgApiTypeDTOContract $update */
    $update = $mapper->fromArray(TgApi\Types\DTO\UpdateTypeDTO::class, $updateRaw);
    $processor->process($update, $botId, $config);
    echo "---\n";
}

echo "\nDone!\n";
