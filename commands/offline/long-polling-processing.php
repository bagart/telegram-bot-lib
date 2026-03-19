<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;

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
    'help',
    'log-level::',
]), [
    'token::',
    'echo',
    'show',
    'store',
    'log',
    'dbg',
    'antispam',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/offline/long-polling-processing.php   # simulate processing with example payloads

Options:
  --echo                                           # echo reply to messages
  --show                                           # dump update objects
  --store                                          # store messages to database
  --log                                            # log messages to stderr
  --dbg                                            # dump DTO to stdout (any type)
  --antispam                                       # validate messages for spam/advertising

  --log-level=debug|info|warning|error             # minimum log level (default: info)
  --help
";
    exit(0);
}

$token = '123456789:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

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
    logLevel: (string)($options['log-level'] ?? null) ?: ASKLogWrapper::LEVEL_DEFAULT,
);
CommandActions::makePollerConfig(options: $options, serviceConfig: $tgConfig);
CommandActions::configInfo(tgConfig: $tgConfig, initProcConfig: $initProcConfig);

$botSetup = $factory->create(
    serviceConfig: $tgConfig,
    initProcConfig: $initProcConfig,
);

$selector = new RegisteredUpdateProcessorSelector(
    serviceConfig: $tgConfig,
    botSetup: $botSetup,
);

// DTO mapper for converting raw arrays to UpdateTypeDTO
$dtoMapper = $factory->dtoMapper($tgConfig);

// --- Simulate processing using example payloads ---
$payloads = getWebhookPayloads();

echo "Loaded ".count($payloads)." example payload(s)\n";
echo str_repeat('-', 40)."\n";
$botConfig = new TgBotConfig(token: $token);
$lastId = 0;
foreach ($payloads as $index => $payload) {
    $updateId = $payload['update_id'] ?? 0;
    echo sprintf("[%d/%d] update_id=%d\n", $index + 1, count($payloads), $updateId);

    try {
        $updateDTO = $dtoMapper->fromArray(UpdateTypeDTO::class, $payload);
        assert($updateDTO instanceof UpdateTypeDTO);

        $lastId = max($lastId, $updateDTO->updateId + 1);

        $selectProcessors = $selector->selectProcessors($updateDTO, $botConfig);
        foreach ($selectProcessors as $property => $processors) {
            foreach ($processors as $processor) {
                $isStrictOrdered = $processor->isStrictOrdered(
                    dto: $updateDTO,
                    botConfig: $botConfig,
                    action: $property,
                );
                echo " $property -> ".$processor::class.($isStrictOrdered ? ' (strict)' : '').")\n";
            }
        }
    } catch (\Throwable $e) {
        $botSetup->logger->error("process payload error ".$e::class.": ".$e->getMessage());
        echo "  [ERROR] {$e->getMessage()}\n";
    }
}

echo str_repeat('-', 40)."\n";
echo "Done. Processed ".count($payloads)." payload(s), last update_id: {$lastId}\n\n";
