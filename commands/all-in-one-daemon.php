<?php

declare(strict_types=1);

use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\AsyncKernel\ASKClock;
use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\AsyncKernel\Backpressure\ASKBackpressureStrategyDynamicSkip;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\TelegramBot\ApiCommunication\Polling\TgPollerDaemon;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgAdvancedRateLimiter;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgRateLimiterRegistry;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgPollerConfig;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\Adapters\OutboundRateLimiterAdapter;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\ExpiryMiddleware;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundQueueRegistry;
use BAGArt\TelegramBot\Outbound\RateLimitMiddleware;
use BAGArt\TelegramBot\Outbound\RetryBudgetMiddleware;
use BAGArt\TelegramBot\Outbound\TelegramOutboundExecutor;
use BAGArt\TelegramBot\Outbound\TgOutboundDaemon;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;
use BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions\LogErrorAction;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorConsumer;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorRegistry;
use BAGArt\TelegramBot\Processing\ProcessorUpdateDaemon;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\Update\UpdateRouter;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../vendor/autoload.php';

$allowedOptions = [
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
    'dispatcher::',
    'transport::',
    'log-level::',
    'cache-driver::',
];

$options = CommandActions::parseOptions(getopt('', $allowedOptions), $allowedOptions);

if (isset($options['help'])) {
    echo 'Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx                    # Default Telegram Token

php commands/all-in-one-daemon.php                   # All-in-one daemon (poller + processor + outbound)

Options:
  --token=xxx:xxx                                    # use custom token

  --echo                                             # processor: echo reply to messages
  --store                                            # processor: store messages to database
  --log                                              # processor: log messages to stderr
  --show                                             # processor: dump update objects
  --dbg                                              # processor: dump DTO to stdout (any type)
  --antispam                                         # processor: validate messages for spam/advertising

  --no-ack                                           # read without acknowledging updates
  --turbo                                            # Fast mode: Ack before process (work only with async mode)

  --transport=guzzle|curl-multi|ask-socket           # transport selection (default: ask-socket)
  --dispatcher=sync|async|queue                      # processors dispatcher

  --cache-driver=memory|file|apcu                   # cache backend (default: memory)
  --memory-limit=512M                                # PHP memory limit
  --log-level=debug|info|warning|error               # minimum log level (default: info)
  --help
';
    exit(0);
}

$token = CommandActions::resolveToken($options);

$botSetupFactory = TgBotSetupFactory::build();

$botSetup = $botSetupFactory->createFromOptions(
    options: $options,
);

$pollerConfig = new TgPollerConfig(
    noAck: array_key_exists('no-ack', $options),
    allowedMaxInboxSizeToPoll: array_key_exists('turbo', $options) ? 100 : 0,
);

CommandActions::verifyBot(
    dtoClient: $botSetup->dtoClient,
    token: $token,
    logger: $botSetup->logger,
);

$kernel = new AsyncKernel(
    logger:$botSetup->logger,
    shutdownTimeout: 60 * 60,
);

$kernel->addDaemon(
    new TgPollerDaemon(
        botConfig: new TgBotConfig(token: $token),
        queue: $botSetup->queue,
        dtoClient: $botSetup->dtoClient,
        updateProcessorSelector: new RegisteredUpdateProcessorSelector(
            serviceConfig: $botSetup->serviceConfig,
            botSetup: $botSetup,
        ),
        logger: $botSetup->logger,
        pollerConfig: $pollerConfig,
        processingStatistics: $botSetup->processingStatistics,
        backpressureStrategy: new ASKBackpressureStrategyDynamicSkip(),
    )
);

$kernel->addDaemon(
    new ProcessorUpdateDaemon(
        queue: $botSetup->queue,
        updateRouter: new UpdateRouter(
            serviceConfig: $botSetup->serviceConfig,
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
);

$workerConfig = new OutboundWorkerConfig();

$clock = new ASKClock();
$cache = TgBotSetupFactory::createCache(serviceConfig: $botSetup->serviceConfig);
$locker = new InMemoryLocker();
$outboundCache = new KernelCacheAdapter($cache, $locker);

$redisDsn = $botSetup->serviceConfig->redisDsn;
$outboundQueueType = $botSetup->serviceConfig->outboundQueueStore
    ?? ($redisDsn !== null ? 'redis' : 'in_memory');

$outboundQueue = OutboundQueueRegistry::build()->make(
    type: $outboundQueueType,
    clock: $clock,
    dsn: $redisDsn,
    maxSize: $workerConfig->maxConcurrentFibers * 20,
);

$rateLimiterType = $botSetup->serviceConfig->rateLimiter ?? TgAdvancedRateLimiter::NAME;
$rateLimiter = new OutboundRateLimiterAdapter(
    limiter: TgRateLimiterRegistry::build()->make($rateLimiterType, $cache)
    ?? new TgAdvancedRateLimiter(cache: $cache),
);

$pipeline = new OutboundPipeline([
    new ExpiryMiddleware(
        maxAgeSec: $workerConfig->maxAgeSec,
        minAttemptsForExpiry: $workerConfig->minAttemptsForExpiry,
        clock: $clock,
    ),
    new RetryBudgetMiddleware(maxAttempts: $workerConfig->maxAttempts),
    new RateLimitMiddleware($rateLimiter),
    new TelegramOutboundExecutor(
        dtoClient: $botSetup->dtoClient,
        rateLimiter: $rateLimiter,
        dtoMapper: $botSetupFactory->dtoMapper($botSetup->serviceConfig),
    ),
]);

$stats = new TgOutboundStats($outboundCache, $workerConfig->metricsRetentionHours);
$circuitBreaker = new OutboundCircuitBreaker(
    $outboundCache,
    $workerConfig->cbFailureThreshold,
    $workerConfig->cbOpenTimeoutSec
);
$leaseRenewer = new LeaseRenewer($outboundQueue, $clock, $workerConfig->renewalIntervalSec, $workerConfig->maxRenewals);

$kernel->addDaemon(
    new TgOutboundDaemon(
        queue: $outboundQueue,
        pipeline: $pipeline,
        circuitBreaker: $circuitBreaker,
        stats: $stats,
        leaseRenewer: $leaseRenewer,
        logger: $botSetup->logger,
        config: $workerConfig,
        scheduler: new ASKFiberScheduler(),
    )
);

$kernel->run();
