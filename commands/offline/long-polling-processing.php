<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/../includes/validate-options.php';
require_once __DIR__.'/../includes/resolve-token.php';
require_once __DIR__.'/../includes/verify-bot.php';

$options = parseCommandOptions([
    'token::',
    'echo',
    'store',
    'log',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/offline/long-polling-processing.php   # receive updates with DTOProcessor

Options:
  --help
  --echo                                           # echo reply to messages
  --store                                          # store messages to database
  --log                                            # log messages to stderr
  --token=xxx:xxx                                  # use custom token
  --log-level=debug|info|warning|error             # minimum log level (default: info)
";
    exit(0);
}

$token = getCommandToken($options);
$botId = explode(':', $token)[0];

// --- Setup ---
$echo = array_key_exists('echo', $options);
$store = array_key_exists('store', $options);
$log = array_key_exists('log', $options);

$tgDTOClient = TgPureFactory::dtoClient();
$logger = TgPureFactory::logger();
$config = new TgUpdateExampleConfig(
    bot: new TgBotConfig(token: $token),
    dispatcher: TgPureFactory::syncDispatcherType(),
);
initUpdatePollerConfig($options, $config);

$processor = new UpdateDTOInitProcessor(
    processorRegistry: TgPureFactory::processorRegistry(config: $config),
    dispatcherRegistry: PipelineDispatcherRegistry::build(),
    logger: $logger,
);

echo "=== Long Polling Processing Mode ===\n";
$flags = implode(' ', array_filter([
    $echo ? '[ECHO]' : null,
    $store ? '[STORE]' : null,
    $log ? '[LOG]' : null,
]));
echo "Flags: {$flags}\n\n";

$lastId = 0;
try {
    $response = $tgDTOClient->request(
        $token,
        new TgApi\Methods\DTO\GetUpdatesMethodDTO(
            offset: $lastId,
            limit: 100,
            timeout: 60,
            allowedUpdates: ['message', 'callback_query'],
        ),
    );

    if ($response->ok) {
        foreach ($response->result as $updateDTO) {
            assert($updateDTO instanceof TgApi\Types\DTO\UpdateTypeDTO);
            $lastId = max($lastId, $updateDTO->updateId + 1);
            $processor->process($updateDTO, $botId, $config);
        }
    } else {
        $logger->error("tg api getUpdates response not ok: ".json_encode($response, JSON_PRETTY_PRINT));
        echo '?';
    }
} catch (\Throwable $e) {
    $logger->error("tg api getUpdates error ".$e::class.": ".$e->getMessage());
    echo '*';
}

echo "\nDone of long poller example.\n\n";
