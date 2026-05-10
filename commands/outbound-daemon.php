<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\Scheduler\FiberScheduler;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRateLimiter;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundDaemon;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgRequestOrderingManager;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlMultiTransport;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotRedisQueueWrapper;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';

$options = parseCommandOptions([
    'redis-host::',
    'redis-port::',
    'request-queue::',
    'memory-limit::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/outbound-daemon.php              # Start outbound request daemon

Options:
  --help
  --token=xxx:xxx                             # use custom token
  --redis-host=127.0.0.1                      # Redis host (default: 127.0.0.1)
  --redis-port=6379                           # Redis port (default: 6379)
  --request-queue=tg-outbound-requests        # Queue name for outbound requests
  --memory-limit=512M                         # PHP memory limit
  --log-level=debug|info|warning|error        # minimum log level (default: info)
";
    exit(0);
}

ini_set('memory_limit', (string)($options['memory-limit'] ?? '512M'));
function_exists('pcntl_async_signals') && pcntl_async_signals(true);

$requestQueue = $options['request-queue'] ?? 'tg-outbound-requests';
$config = new TgBotConfig(
    token: '',
    logLevel: (string)($options['log-level'] ?? null) ?: TgBotLogWrapper::LEVEL_DEFAULT
);

$logger = TgPureFactory::logger($config);

$queue = TgBotRedisQueueWrapper::build(
    requestQueue: $requestQueue,
    logger: $logger,
);
$transport = new TgCurlMultiTransport(
    logger: $logger,
);
$scheduler = new FiberScheduler(
    transport: $transport,
    logger: $logger,
);

$daemon = new TgOutboundDaemon(
    consumer: $queue,
    producer: $queue,
    scheduler: $scheduler,
    dtoClient: TgBotApiDTOClient::build(
        transport: $transport,
        logger: $logger,
    ),
    ordering: new TgRequestOrderingManager(
        scheduler: $scheduler,
        logger: $logger,
    ),
    rateLimiter: new TgRateLimiter(
        cache: TgPureFactory::cache(),
    ),
    retryPolicy: new TgRetryPolicy(),
    logger: $logger,
);

$logger->info("TgOutboundDaemon started.\n  Transport: ".$transport::class."\n  Request queue: {$requestQueue}\nPress Ctrl+C to stop.");

$daemon->run();
