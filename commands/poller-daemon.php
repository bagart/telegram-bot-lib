<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\TelegramBot\ApiCommunication\Polling\PollerState;
use BAGArt\TelegramBot\ApiCommunication\Polling\TgPollerDaemon;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\RedisQueueConfig;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgPollerConfig;
use BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions\LogErrorAction;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorConsumer;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorRegistry;
use BAGArt\TelegramBot\Processing\ProcessorUpdateDaemon;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\Update\UpdateRouter;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../vendor/autoload.php';

$allowedOptions = [
    'sync',
    'async',
    'queue',
    'token::',
    'echo',
    'show',
    'store',
    'log',
    'dbg',
    'no-ack',
    'help',
    'turbo',
    'antispam',
    'poller::',
    'dispatcher::',
    'transport::',
    'log-level::',
    'redis-host::',
    'redis-port::',
    'redis-timeout::',
    'redis-prefix::',
    'outbound-queue::',
    'processor-queue::',
    'block-timeout::',
];

$options = CommandActions::parseOptions(getopt('', $allowedOptions), $allowedOptions);

if (isset($options['help'])) {
    echo 'Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx                    # Default Telegram Token

php commands/poller-daemon.php                       # Telegram poller daemon (queued mode)

Options:
  --token=xxx:xxx                                    # use custom token

  --echo                                             # processor: echo reply to messages
  --store                                            # processor: store messages to database
  --log                                              # processor: log messages to stderr
  --show                                             # processor: dump update objects
  --dbg                                              # processor: dump DTO to stdout (any type)
  --antispam                                         # processor: validate messages for spam/advertising

  --sync                                             # sync long-poll mode
  --async                                            # async fiber-based polling mode
  --queue                                            # use LaravelProcessingDispatcher

  --no-ack                                           # read without acknowledging updates
  --turbo                                            # Fast mode: Ack before process (work only with async mode)

  --poller=                                          # poller selection (sync|async|custom)
  --transport=guzzle|curl-multi|ask-socket          # transport selection (default: ask-socket)
  --dispatcher=sync|async|queue                      # processors dispatcher

  --redis-host=127.0.0.1                             # Redis host (default: 127.0.0.1)
  --redis-port=6379                                  # Redis port (default: 6379)
  --redis-timeout=2.0                                # Redis connection timeout
  --redis-prefix=tg:                                 # Redis key prefix
  --outbound-queue=tg-outbound-requests              # Outbound queue name
  --processor-queue=tg-processor-jobs                # Processor queue name
  --block-timeout=2                                  # BLPOP block timeout in seconds

  --memory-limit=512M                                # PHP memory limit
  --log-level=debug|info|warning|error               # minimum log level (default: info)
  --help
';
    exit(0);
}

$token = CommandActions::resolveToken($options);

$processorRegistry = TgBotSetupFactory::processorRegistry(
    new ProcessorConfig(
        echo: array_key_exists('echo', $options),
        show: array_key_exists('show', $options),
        log: array_key_exists('log', $options),
        store: array_key_exists('store', $options),
        dbg: array_key_exists('dbg', $options),
        antispam: array_key_exists('antispam', $options),
    )
);
$pollerConfig = new TgPollerConfig(
    noAck: array_key_exists('no-ack', $options),
    allowedMaxInboxSizeToPoll: array_key_exists('turbo', $options) ? 1000 : 0,
);
$serviceConfig = CommandActions::makePollerConfig(
    options: $options,
);
CommandActions::configInfo($serviceConfig);

$factory = TgBotSetupFactory::build();
$queueConfig = RedisQueueConfig::fromOptions($options);
$botSetup = $factory->createQueued(
    queueConfig: $queueConfig,
    serviceConfig: $serviceConfig,
);

CommandActions::verifyBot($botSetup->dtoClient, $token);

new AsyncKernel($botSetup->logger)
    ->addDaemon(
        new TgPollerDaemon(
            botConfig: new TgBotConfig(token: $token),
            queue: $botSetup->queue,
            dtoClient: $botSetup->dtoClient,
            updateProcessorSelector: new RegisteredUpdateProcessorSelector(
                serviceConfig: $serviceConfig,
                botSetup: $botSetup,
            ),
            logger: $botSetup->logger,
            pollerConfig: $pollerConfig,
            pollerState: new PollerState(),
            processingStatistics: $botSetup->processingStatistics,
        )
    )
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
