<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\ASKClient\Client\ApiClient;
use BAGArt\ASKClient\Client\CurlMultiClient;
use BAGArt\ASKClient\Client\GuzzleClient;
use BAGArt\ASKClient\Client\HttpsSocketClient\HttpsSocketClient;
use BAGArt\ASKClient\Client\HttpsSocketClient\HttpsSocketClientConfig;
use BAGArt\ASKClient\Contracts\Client\NetworkClientContract;
use BAGArt\ASKClient\Contracts\Transporting\HttpTransportContract;
use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\ASKClient\RateLimiter\ASKRateLimiter;
use BAGArt\ASKClient\Transporting\HttpTransports\ASKSocketTransport;
use BAGArt\ASKClient\Transporting\HttpTransports\CurlMultiTransport;
use BAGArt\ASKClient\Transporting\HttpTransports\GuzzleTransport;
use BAGArt\ASKClient\Transporting\TransportRegistry;
use BAGArt\ASKClientRedis\Connection\FiberRedisConnection;
use BAGArt\ASKClientRedis\Redis\Client\AsyncFiberRedisClient;
use BAGArt\ASKClientRedis\Redis\Client\PhpRedisAdapter;
use BAGArt\ASKClientRedis\Redis\Connector\PhpRedisConnector;
use BAGArt\ASKClientRedis\Redis\Contract\RedisClientContract;
use BAGArt\ASKClientRedis\Redis\RedisDsn;
use BAGArt\AsyncKernel\ASKClock;
use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\AsyncKernel\Promise\ASKPromiseResolver;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\ApiCommunication\Clients\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Polling\ProcessingStatistics;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgAdvancedRateLimiter;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgRateLimiterRegistry;
use BAGArt\TelegramBot\ApiCommunication\Transports\TgBotApiTransport;
use BAGArt\TelegramBot\Configs\DaemonRuntime;
use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\Queue\QueueConfigContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\Adapters\OutboundRateLimiterAdapter;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\ExpiryMiddleware;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\Ordering\DefaultOrderingStrategy;
use BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundQueueRegistry;
use BAGArt\TelegramBot\Outbound\RateLimitMiddleware;
use BAGArt\TelegramBot\Outbound\RetryBudgetMiddleware;
use BAGArt\TelegramBot\Outbound\TelegramOutboundExecutor;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;
use BAGArt\TelegramBot\Outbound\TgSender;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\AsyncFiberProcessingDispatcher;
use BAGArt\TelegramBot\Processing\Processors\DbgDTOToLoggerProcessor;
use BAGArt\TelegramBot\Processing\Processors\DbgDTOToStdProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageDTOEchoToUserProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageDTOShowToConsoleProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageDTOToDbProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidatorProcessor;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final readonly class TgBotSetupFactory
{
    public function __construct(
        public ?string $loggerChannel,
        public ASKLogWrapper $logger,
        public ASKCacheWrapper $cache,
        public ProcessingDispatcherRegistry $dispatcherRegistry,
        public TgRateLimiterRegistry $rateLimiterRegistry,
        private TypeDTOProcessorRegistry $processorRegistry,
        public TgEntityToDTORegistry $tgEntityToDTORegistry,
    ) {
    }

    public static function build(
        ?ProcessorConfig $initProcConfig = null,
        ?string $loggerChannel = null,
        ?ASKLogWrapper $logger = null,
        ?ASKCacheWrapper $cache = null,
    ): self {
        return new self(
            loggerChannel: $loggerChannel,
            logger: $logger ?? self::createLogger(),
            cache: $cache ?? self::createCache(),
            dispatcherRegistry: ProcessingDispatcherRegistry::build(),
            rateLimiterRegistry: TgRateLimiterRegistry::build(),
            processorRegistry: self::processorRegistry($initProcConfig),
            tgEntityToDTORegistry: TgEntityToDTORegistry::build(),
        );
    }

    public function createFromOptions(
        array $options = [],
        array $env = [],
    ): TgBotSetup {
        $env = $env ?: $_ENV ?: getenv();

        $configurator = new EnvServiceConfigurator($options, $env);
        $serviceConfig = $configurator->getServiceConfig();

        $transport = self::buildSocketTransport($serviceConfig, $this->logger, $env);

        if ($transport instanceof ASKSocketTransport) {
            self::warmSocketPool($transport, $this->logger, $env);
        }

        $procConfig = new ProcessorConfig(
            echo: array_key_exists('echo', $options),
            show: array_key_exists('show', $options),
            log: array_key_exists('log', $options),
            store: array_key_exists('store', $options),
            dbg: array_key_exists('dbg', $options),
            antispam: array_key_exists('antispam', $options),
        );

        return self::build($procConfig)
            ->createAllInOne(
                serviceConfig: $serviceConfig,
                transport: $transport,
            );
    }

    private static function buildSocketTransport(
        TgServiceConfig $config,
        ASKLogWrapper $logger,
        array $env,
    ): ?ASKSocketTransport {
        $isPoolEnabled = ($env['TG_OUTBOUND_SOCKET_POOL'] ?? null) === '1';

        if (!$isPoolEnabled || $config->transport !== ASKSocketTransport::TYPE) {
            return null;
        }

        $transport = ASKSocketTransport::withConfig(
            new HttpsSocketClientConfig(
                keepAlive: true,
                maxIdlePerHost: (int)($env['TG_OUTBOUND_MAX_IDLE_PER_HOST'] ?? 8),
                maxIdleTotal: (int)($env['TG_OUTBOUND_MAX_IDLE_TOTAL'] ?? 32),
                idleTimeout: (float)($env['TG_OUTBOUND_IDLE_TIMEOUT'] ?? 60.0),
            ),
        );

        return $transport;
    }

    /**
     * Pre-open kept-alive TLS connections to {@see TG_OUTBOUND_WARM_HOST} so the first
     * outbound requests pay no handshake cost. One-time blocking warmup, called once
     * before the AsyncKernel starts ticking. No-op when warm_connections <= 0 or the
     * pool is disabled.
     */
    public static function warmSocketPool(
        ASKSocketTransport $transport,
        ASKLogWrapper $logger,
        array $env,
    ): int {
        $warmCount = (int)($env['TG_OUTBOUND_WARM_CONNECTIONS'] ?? 0);
        $warmHost = (string)($env['TG_OUTBOUND_WARM_HOST'] ?? 'api.telegram.org');

        if ($warmCount <= 0 || $warmHost === '') {
            return 0;
        }

        $warmed = $transport->warmUp($warmHost, $warmCount);

        $logger->info('TgBotSetup socket pool warmed', [
            'host' => $warmHost,
            'requested' => $warmCount,
            'warmed' => $warmed,
        ]);

        return $warmed;
    }

    public static function processorRegistry(?ProcessorConfig $config = null): TypeDTOProcessorRegistry
    {
        if (!$config) {
            return TypeDTOProcessorRegistry::build();
        }

        return TypeDTOProcessorRegistry::build([
            MessageTypeDTO::class => array_keys(array_filter([
                MessageValidatorProcessor::class => $config->antispam,
                MessageDTOEchoToUserProcessor::class => $config->echo,
                DbgDTOToLoggerProcessor::class => $config->log,
                MessageDTOToDbProcessor::class => $config->store,
                MessageDTOShowToConsoleProcessor::class => $config->show,
                DbgDTOToStdProcessor::class => $config->dbg,
            ])),
        ]);
    }

    public function scheduler(): ASKSchedulerContract
    {
        return new ASKFiberScheduler();
    }

    public function createAllInOne(
        ?TgServiceConfig $serviceConfig = null,
        ?HttpTransportContract $transport = null,
    ): TgBotSetup {
        $serviceConfig ??= new TgServiceConfig();
        $serviceConfig->daemonRuntime = new DaemonRuntime(
            scheduler: DaemonRuntime::MODE_ASYNC,
        );
        $serviceConfig->processingEngine = 'in_memory';
        $serviceConfig->cacheDriver = 'memory';

        return $this->create(
            serviceConfig: $serviceConfig,
            transport: $transport,
        );
    }

    public function createQueued(
        QueueConfigContract $queueConfig,
        ?TgServiceConfig $serviceConfig = null,
        ?ASKSchedulerContract $scheduler = null,
        ?HttpTransportContract $transport = null,
    ): TgBotSetup {
        $serviceConfig ??= new TgServiceConfig();
        $serviceConfig->daemonRuntime = new DaemonRuntime(
            scheduler: DaemonRuntime::MODE_QUEUE,
            queue: $queueConfig,
        );
        $serviceConfig->processingEngine = 'redis';

        return $this->create(
            $serviceConfig,
            scheduler: $scheduler,
            transport: $transport,
        );
    }

    public function create(
        ?TgServiceConfig $serviceConfig = null,
        ?ProcessorConfig $initProcConfig = null,
        ?ASKSchedulerContract $scheduler = null,
        ?HttpTransportContract $transport = null,
        ?TypeDTOProcessorRegistry $processorRegistryOverride = null,
    ): TgBotSetup {
        $serviceConfig ??= new TgServiceConfig();
        $logger = $this->logger;
        $cache = $this->cache ?? self::createCache($serviceConfig);

        $transport ??= TransportRegistry::build()->make($serviceConfig->transport);

        $tgTransport = new TgBotApiTransport($transport);

        $redisClient = null;

        if ($serviceConfig->processingEngine === 'redis') {
            $queueConfig = $serviceConfig->daemonRuntime->queue;

            $dsn = new RedisDsn(
                host: $queueConfig?->host() ?? (string)getenv('TG_LIB_REDIS_HOST') ?: '127.0.0.1',
                port: $queueConfig?->port() ?? (int)getenv('TG_LIB_REDIS_PORT') ?: 6379,
                timeout: $queueConfig?->timeout() ?? (float)getenv('TG_LIB_REDIS_TIMEOUT') ?: 2.0,
            );

            $serviceConfig->redisDsn = $dsn->toString();

            if ($serviceConfig->daemonRuntime->scheduler === DaemonRuntime::MODE_ASYNC) {
                if (!$scheduler instanceof ASKFiberScheduler) {
                    throw new \InvalidArgumentException(
                        'Async Redis requires ASKFiberScheduler, got '.get_debug_type($scheduler),
                    );
                }

                $connection = new FiberRedisConnection(
                    scheduler: $scheduler,
                    host: $dsn->host,
                    port: $dsn->port,
                    timeout: $dsn->timeout,
                );

                $redisClient = new AsyncFiberRedisClient(
                    connection: $connection,
                    password: $dsn->password,
                    database: $dsn->database,
                );

                $redisClient->warm();
            } else {
                $redis = (new PhpRedisConnector())->connect($dsn);
                $redisClient = new PhpRedisAdapter($redis);
            }
        }

        $queue = QueueAdapterRegistry::build()->make(
            type: $serviceConfig->processingEngine,
            dsn: $serviceConfig->redisDsn,
            redis: $redisClient,
        );

        $apiClient = new ApiClient(
            transport: self::resolveNetworkClient($transport),
            rateLimiter: new ASKRateLimiter($cache, new ASKClock()),
            promiseResolver: new ASKPromiseResolver(),
        );

        $processorRegistry = $processorRegistryOverride
            ?? ($initProcConfig !== null ? self::processorRegistry($initProcConfig) : $this->processorRegistry);

        $dtoClient = TgBotApiDTOClient::build(transport: $tgTransport, logger: $logger);
        $dtoMapper = $this->dtoMapper($serviceConfig);

        $outbound = $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $serviceConfig->outboundWorkerConfig,
            redisDsn: $serviceConfig->redisDsn,
            redisClient: $redisClient,
            rateLimiterType: $serviceConfig->rateLimiter,
            dtoClient: $dtoClient,
            dtoMapper: $dtoMapper,
        );

        $processorScheduler = new ASKFiberScheduler();

        $tgApiCallerDispatcher = $this->dispatcherRegistry->make(
            dispatcherType: $serviceConfig->dispatcher,
            scheduler: $processorScheduler,
            logger: $logger,
        );

        return new TgBotSetup(
            logger: $logger,
            cache: $cache,
            queue: $queue,
            locker: new InMemoryLocker(),
            transport: $tgTransport,
            dtoClient: $dtoClient,
            tgApiCaller: new TgApiCaller(
                sender: $outbound['sender'],
                dispatcherRegistry: $this->dispatcherRegistry,
                serviceConfig: $serviceConfig,
                logger: $logger,
                dispatcher: $tgApiCallerDispatcher,
            ),
            processorRegistry: $processorRegistry,
            processingStatistics: new ProcessingStatistics(),
            apiClient: $apiClient,
            processorScheduler: $processorScheduler,
            tgSender: $outbound['sender'],
            outboundStats: $outbound['stats'],
            serviceConfig: $serviceConfig,
        );
    }

    public function getDtoClient(
        TgServiceConfig $config,
        ?HttpTransportContract $transport = null
    ): TgBotApiDTOClientContract {
        $transport ??= TransportRegistry::build()->make($config->transport);
        $tgTransport = new TgBotApiTransport($transport);

        return TgBotApiDTOClient::build(
            transport: $tgTransport,
            logger: self::createLogger($config),
        );
    }

    public function dtoMapper(TgServiceConfig $config): TgApiDTOMapper
    {
        $logger = self::createLogger($config);

        return new TgApiDTOMapper(
            tgApiDTORegistry: $this->tgEntityToDTORegistry,
            logger: $logger,
        );
    }

    public static function webhook(
        TypeDTOProcessorRegistry $processorRegistry,
        ?TgBotSetup $setup = null,
        ?TgServiceConfig $serviceConfig = null,
    ): TgWebhookRequestParser {
        $serviceConfig ??= new TgServiceConfig();

        if ($setup === null) {
            $factory = new self(
                loggerChannel: null,
                logger: null,
                cache: null,
                dispatcherRegistry: ProcessingDispatcherRegistry::build(),
                rateLimiterRegistry: TgRateLimiterRegistry::build(),
                processorRegistry: $processorRegistry,
                tgEntityToDTORegistry: TgEntityToDTORegistry::build(),
            );
            $setup = $factory->create(
                serviceConfig: $serviceConfig,
            );
        } else {
            $factory = self::build();
        }

        return new TgWebhookRequestParser(
            tgApiDTOMapper: new TgApiDTOMapper(
                tgApiDTORegistry: $factory->tgEntityToDTORegistry,
                logger: $setup->logger,
            ),
            selector: new RegisteredUpdateProcessorSelector(
                serviceConfig: $serviceConfig,
                botSetup: $setup,
            ),
            secretService: new AutoSecretByTokenService(),
            logger: $setup->logger,
            dispatcherRegistry: $factory->dispatcherRegistry,
        );
    }

    private static function resolveNetworkClient(HttpTransportContract $transport): NetworkClientContract
    {
        return match ($transport::class) {
            GuzzleTransport::class => new GuzzleClient(),
            CurlMultiTransport::class => new CurlMultiClient(),
            ASKSocketTransport::class => new HttpsSocketClient(),
            default => throw new \RuntimeException(
                sprintf(
                    'Cannot resolve network client for transport class: %s',
                    $transport::class,
                )
            ),
        };
    }

    private function needsScheduler(TgServiceConfig $config): bool
    {
        $asyncDispatchers = [
            AsyncFiberProcessingDispatcher::TYPE,
        ];

        return in_array($config->dispatcher, $asyncDispatchers, true);
    }

    public static function createLogger(
        ?TgServiceConfig $serviceConfig = null,
        ?string $channel = null,
    ): ASKLogWrapper {
        return new ASKLogWrapper(
            logger: new Logger(
                name: $channel ?? 'TelegramBot',
                handlers: [
                    new StreamHandler(
                        stream: 'php://stderr',
                        level: Level::Debug,
                    ),
                ],
            ),
            minLevel: $serviceConfig?->logLevel ?? ASKLogWrapper::LEVEL_DEFAULT,
        );
    }

    public static function createCache(?TgServiceConfig $serviceConfig = null): ASKCacheWrapper
    {
        $clock = new ASKClock();

        $driverType = $serviceConfig?->cacheDriver ?? 'file';

        return new ASKCacheWrapper(
            CacheDriverRegistry::build()->make($driverType, $clock),
        );
    }

    /**
     * Build outbound subsystem components (without the daemon).
     *
     * @return array{stats: TgOutboundStats, queue: OutboundQueueContract, sender: TgSenderContract, pipeline: OutboundPipeline, circuitBreaker: OutboundCircuitBreaker, leaseRenewer: LeaseRenewer}
     */
    private function resolveOutboundDeps(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
        ?string $rateLimiterType = TgAdvancedRateLimiter::NAME,
        ?TgBotApiDTOClientContract $dtoClient = null,
        ?TgApiDTOMapperContract $dtoMapper = null,
    ): array {
        $config = $workerConfig ?? new OutboundWorkerConfig();
        $clock = new ASKClock();
        $logger = $this->logger ?? self::createLogger(serviceConfig: $serviceConfig, channel: $this->loggerChannel);

        $cache = $this->cache ?? self::createCache();
        $locker = new InMemoryLocker();
        $outboundCache = new KernelCacheAdapter($cache, $locker);

        $redisDsn = $redisDsn ?? $serviceConfig?->redisDsn;

        $outboundQueueType = $serviceConfig?->outboundQueueStore
            ?? ($redisDsn !== null ? 'redis' : 'in_memory');

        $outboundQueue = OutboundQueueRegistry::build()->make(
            type: $outboundQueueType,
            clock: $clock,
            dsn: $redisDsn,
            maxSize: $config->maxConcurrentFibers * 20,
            redis: $redisClient,
        );

        $rateLimiterImpl = $this->rateLimiterRegistry->make($rateLimiterType, $cache)
            ?? new TgAdvancedRateLimiter(cache: $cache);

        $rateLimiter = new OutboundRateLimiterAdapter($rateLimiterImpl);

        // dtoClient/dtoMapper may be passed from outside (shared transport with poller,
        // which ticks the kernel). Otherwise we build a fresh one — but then the executor
        // is not suitable for async-fiber use (I/O will stall without a ticking transport).
        $dtoClient ??= $this->getDtoClient(new TgServiceConfig());
        $dtoMapper ??= $this->dtoMapper(new TgServiceConfig());

        $pipeline = new OutboundPipeline([
            new ExpiryMiddleware($config->maxAgeSec, $config->minAttemptsForExpiry),
            new RetryBudgetMiddleware($config->maxAttempts),
            new RateLimitMiddleware($rateLimiter),
            new TelegramOutboundExecutor(
                $dtoClient,
                $rateLimiter,
                $dtoMapper,
            ),
        ]);

        $stats = new TgOutboundStats($outboundCache, $config->metricsRetentionHours);
        $circuitBreaker = new OutboundCircuitBreaker(
            $outboundCache,
            $config->cbFailureThreshold,
            $config->cbOpenTimeoutSec
        );
        $leaseRenewer = new LeaseRenewer($outboundQueue, $clock, $config->renewalIntervalSec, $config->maxRenewals);

        $sender = new TgSender($outboundQueue, $this->dtoMapper(new TgServiceConfig()), new DefaultOrderingStrategy());

        return [
            'stats' => $stats,
            'queue' => $outboundQueue,
            'sender' => $sender,
            'pipeline' => $pipeline,
            'circuitBreaker' => $circuitBreaker,
            'leaseRenewer' => $leaseRenewer,
        ];
    }

    public function createOutboundQueue(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): OutboundQueueContract {
        return $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        )['queue'];
    }

    public function createOutboundStats(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): TgOutboundStats {
        return $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        )['stats'];
    }

    public function createOutboundSender(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): TgSenderContract {
        return $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        )['sender'];
    }

    public function createOutboundPipeline(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): OutboundPipeline {
        return $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        )['pipeline'];
    }

    public function createOutboundCircuitBreaker(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): OutboundCircuitBreaker {
        return $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        )['circuitBreaker'];
    }

    public function createLeaseRenewer(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): LeaseRenewer {
        return $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        )['leaseRenewer'];
    }

    /**
     * Return all components needed to build a TgOutboundDaemon.
     * All returned objects share the same connections (queue, cache, etc.).
     *
     * @return array{queue: OutboundQueueContract, pipeline: OutboundPipeline, circuitBreaker: OutboundCircuitBreaker, stats: TgOutboundStats, leaseRenewer: LeaseRenewer}
     */
    public function createOutboundDaemonParts(
        ?TgServiceConfig $serviceConfig = null,
        ?OutboundWorkerConfig $workerConfig = null,
        ?string $redisDsn = null,
        ?RedisClientContract $redisClient = null,
    ): array {
        $deps = $this->resolveOutboundDeps(
            serviceConfig: $serviceConfig,
            workerConfig: $workerConfig,
            redisDsn: $redisDsn,
            redisClient: $redisClient,
        );

        return [
            'queue' => $deps['queue'],
            'pipeline' => $deps['pipeline'],
            'circuitBreaker' => $deps['circuitBreaker'],
            'stats' => $deps['stats'],
            'leaseRenewer' => $deps['leaseRenewer'],
        ];
    }
}
