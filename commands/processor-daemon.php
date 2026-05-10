<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\Scheduler\FiberScheduler;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgRedisQueueProcessorDaemon;
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
    'block-timeout::',
    'memory-limit::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/processor-daemon.php   # Start Redis queue processor daemon

Options:
  --help
  --redis-host=127.0.0.1                      # Redis host (default: 127.0.0.1)
  --redis-port=6379                           # Redis port (default: 6379)
  --request-queue=tg-processor-jobs           # Queue name for processor jobs
  --block-timeout=2                           # BLPOP block timeout in seconds
  --memory-limit=512M                         # PHP memory limit
  --log-level=debug|info|warning|error        # minimum log level (default: info)
";
    exit(0);
}

ini_set('memory_limit', (string)($options['memory-limit'] ?? '512M'));
function_exists('pcntl_async_signals') && pcntl_async_signals(true);

$requestQueue = $options['request-queue'] ?? 'tg-processor-jobs';
$blockTimeout = (int)($options['block-timeout'] ?? 2);
$config = new TgBotConfig(
    token: '',
    logLevel: (string)($options['log-level'] ?? null) ?: TgBotLogWrapper::LEVEL_DEFAULT
);

$logger = TgPureFactory::logger($config);

$queue = TgBotRedisQueueWrapper::build(
    requestQueue: $requestQueue,
    blockTimeout: $blockTimeout,
    logger: $logger,
);
$transport = new TgCurlMultiTransport(
    logger: $logger,
);
$scheduler = new FiberScheduler(
    transport: $transport,
    logger: $logger,
);

$daemon = new TgRedisQueueProcessorDaemon(
    queue: $queue,
    scheduler: $scheduler,
    logger: $logger,
);

$logger->info("TgRedisQueueProcessorDaemon started.\n  Request queue: {$requestQueue}\nPress Ctrl+C to stop.");

$daemon->run();
