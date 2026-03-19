<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\Processing\Processors\UpdateDTOInitProcessor;

require_once __DIR__.'/../../../../../vendor/autoload.php';
require_once __DIR__.'/examples/webhook-payloads.php';

$options = CommandActions::parseOptions(getopt('', [
    'token::',

    'echo',
    'show',
    'store',
    'log',
    'dbg',
    'antispam',

    'log-level::',
    'help',
]), [
    'token::',

    'echo',
    'show',
    'store',
    'log',
    'dbg',
    'antispam',

    'log-level::',
    'help',
]);

if (isset($options['help'])) {
    echo "Usage:

php commands/offline/webhook-processing.php      # simulate webhook processing with example payloads
  --help
  --echo                                         # echo reply to messages
  --show                                         # dump update objects
  --store                                        # store messages to database
  --log                                          # log messages to stderr
  --dbg                                          # dump DTO to stdout (any type)
  --antispam                                     # validate messages for spam/advertising
  --log-level=debug|info|warning|error           # minimum log level (default: info)
";
    exit(0);
}

$token = '12345:XXXX';

$initProcConfig = new ProcessorConfig(
    echo: array_key_exists('echo', $options),
    show: array_key_exists('show', $options),
    log: array_key_exists('log', $options),
    store: array_key_exists('store', $options),
    dbg: array_key_exists('dbg', $options),
    antispam: array_key_exists('antispam', $options),
);

$factory = TgBotSetupFactory::build();
$tgConfig = new TgServiceConfig(
);

CommandActions::makePollerConfig(options: $options, serviceConfig: $tgConfig);
CommandActions::configInfo($tgConfig, $initProcConfig);

$botSetup = $factory->create(serviceConfig: $tgConfig);

$processor = UpdateDTOInitProcessor::build(
    serviceConfig: $tgConfig,
    botSetup: $botSetup,
);

// DTO mapper for converting raw arrays to UpdateTypeDTO
$dtoMapper = $factory->dtoMapper($tgConfig);

echo "=== Webhook Processing Mode (simulated) ===\n";

// --- Simulate processing using example payloads ---
$payloads = getWebhookPayloads();

echo "Loaded ".count($payloads)." example payload(s)\n";
echo str_repeat('-', 40)."\n";

$lastId = 0;
foreach ($payloads as $index => $payload) {
    $updateId = $payload['update_id'] ?? 0;
    echo sprintf("[%d/%d] update_id=%d\n", $index + 1, count($payloads), $updateId);

    try {
        $updateDTO = $dtoMapper->fromArray(UpdateTypeDTO::class, $payload);
        assert($updateDTO instanceof UpdateTypeDTO);

        $lastId = max($lastId, $updateDTO->updateId + 1);
        $processor->process($updateDTO, new TgBotConfig(token: $token));
    } catch (\Throwable $e) {
        $botSetup->logger->error("process payload error ".$e::class.": ".$e->getMessage());
        echo "  [ERROR] {$e->getMessage()}\n";
    }
}

echo str_repeat('-', 40)."\n";
echo "Done. Processed ".count($payloads)." payload(s), last update_id: {$lastId}\n\n";
