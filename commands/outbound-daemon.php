<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\TgOutboundDaemon;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../vendor/autoload.php';

$allowedOptions = [
    'mode::',
    'redis-host::',
    'redis-port::',
    'redis-timeout::',
    'redis-prefix::',
    'memory-limit::',
    'log-level::',
    'help',
];

$options = CommandActions::parseOptions(
    getopt('', $allowedOptions),
    $allowedOptions,
);

CommandActions::initRuntime($options);

if (isset($options['help'])) {
    echo 'Usage:
php commands/outbound-daemon.php              # Start outbound worker daemon

Modes:
  --mode=single                               # Single instance (in-memory state, default)
  --mode=multi                                # Multiple instances (Redis state, requires Redis)

Redis options (required for --mode=multi):
  --redis-host=127.0.0.1                      # Redis host (default: 127.0.0.1)
  --redis-port=6379                           # Redis port (default: 6379)
  --redis-timeout=2.0                         # Redis connection timeout
  --redis-prefix=tg:                          # Redis key prefix

General options:
  --memory-limit=512M                         # PHP memory limit
  --log-level=debug|info|warning|error        # minimum log level (default: info)
  --help
';
    exit(0);
}

$serviceConfig = CommandActions::makeOutboundConfig($options);
$logger = TgBotSetupFactory::createLogger($serviceConfig, 'OutboundDaemon');

$workerConfig = new OutboundWorkerConfig();
$factory = TgBotSetupFactory::build(loggerChannel: 'OutboundDaemon');

$parts = $factory->createOutboundDaemonParts(
    serviceConfig: $serviceConfig,
    workerConfig: $workerConfig,
);

new AsyncKernel(logger: $logger)
    ->addDaemon(
        new TgOutboundDaemon(
            queue: $parts['queue'],
            pipeline: $parts['pipeline'],
            circuitBreaker: $parts['circuitBreaker'],
            stats: $parts['stats'],
            leaseRenewer: $parts['leaseRenewer'],
            logger: $logger,
            config: $workerConfig,
            scheduler: new ASKFiberScheduler(),
        )
    )
    ->run();
