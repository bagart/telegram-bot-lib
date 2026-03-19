<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\RedisQueueConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions\LogErrorAction;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorConsumer;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorRegistry;
use BAGArt\TelegramBot\Processing\ProcessorUpdateDaemon;
use BAGArt\TelegramBot\Processing\Update\UpdateRouter;
use BAGArt\TelegramBot\Queue\JobHandlerFactory;
use BAGArt\TelegramBot\Queue\ASKQueueDaemon;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../vendor/autoload.php';

$allowedOptions = [
    'redis-host::',
    'redis-port::',
    'redis-timeout::',
    'redis-prefix::',
    'outbound-queue::',
    'processor-queue::',
    'block-timeout::',
    'memory-limit::',
    'help',
    'log-level::',
];

$options = CommandActions::parseOptions(getopt('', $allowedOptions), $allowedOptions);

CommandActions::initRuntime($options);

if (isset($options['help'])) {
    echo 'Usage:
php commands/processor-daemon.php   # Start Redis queue processor daemon

Options:
  --redis-host=127.0.0.1                      # Redis host (default: 127.0.0.1)
  --redis-port=6379                           # Redis port (default: 6379)
  --redis-timeout=2.0                         # Redis connection timeout
  --redis-prefix=tg:                          # Redis key prefix
  --processor-queue=tg-processor-jobs         # Queue name for processor jobs
  --block-timeout=2                           # BLPOP block timeout in seconds

  --memory-limit=512M                         # PHP memory limit
  --log-level=debug|info|warning|error        # minimum log level (default: info)
  --help
';
    exit(0);
}

$requestQueue = $options['processor-queue'] ?? getenv('TG_LIB_REDIS_PROCESSOR_QUEUE') ?: 'tg-processor-jobs';
$config = new TgServiceConfig(
    logLevel: (string)($options['log-level'] ?? null) ?: ASKLogWrapper::LEVEL_DEFAULT,
);

$handlerFactory = new JobHandlerFactory(allowCache: true);

$factory = TgBotSetupFactory::build();
$queueConfig = RedisQueueConfig::fromOptions($options);
$botSetup = $factory->createQueued(
    queueConfig: $queueConfig,
    serviceConfig: $config,
);
$serviceConfig = new TgServiceConfig();

$queueDaemon = new ASKQueueDaemon(
    queue: $botSetup->queue,
    scheduler: $factory->scheduler(),
    logger: $botSetup->logger,
    handlerFactory: $handlerFactory,
    queueName: $requestQueue,
);

new AsyncKernel(logger: $botSetup->logger)
    ->addDaemon($queueDaemon)
    ->addDaemon(
        new ProcessorUpdateDaemon(
            queue: $botSetup->queue,
            updateRouter: new UpdateRouter(
                serviceConfig: $serviceConfig,
                botSetup: $botSetup,
                errorConsumer: new ProcessingErrorConsumer(
                    registry: new ProcessingErrorRegistry()
                        ->setDefaults(
                            new LogErrorAction(
                                logger: $botSetup->logger,
                            ),
                        ),
                ),
            ),
            logger: $botSetup->logger,
            processorScheduler: $botSetup->processorScheduler,
        )
    )
    ->run();
