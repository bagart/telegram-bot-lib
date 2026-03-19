<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\ASKClient\ASKClient;
use BAGArt\ASKClient\Client\ApiClient;
use BAGArt\ASKClient\Client\HttpsSocketClient\HttpsSocketClient;
use BAGArt\ASKClient\Client\HttpsSocketClient\HttpsSocketClientConfig;
use BAGArt\ASKClient\Client\Services\PoolWarmer;
use BAGArt\ASKClient\Contracts\Client\ApiClientContract;
use BAGArt\ASKClient\Contracts\Transporting\HttpTransportContract;
use BAGArt\ASKClient\RateLimiter\ASKRateLimiter;
use BAGArt\ASKClient\Transporting\HttpTransports\ASKSocketTransport;
use BAGArt\ASKClient\Transporting\TransportRegistry;
use BAGArt\AsyncKernel\ASKClock;
use BAGArt\AsyncKernel\Promise\ASKPromiseResolver;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgBotApiAskTransport;
use BAGArt\TelegramBot\ApiCommunication\Clients\TgBotApiClient;
use BAGArt\TelegramBot\ApiCommunication\Clients\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgCircuitBreaker;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRequestFactory;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgRateLimiterRegistry;
use BAGArt\TelegramBot\ApiCommunication\TgResponseNormalizer;
use BAGArt\TelegramBot\ApiCommunication\Transports\TgBotApiTransport;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgResponseNormalizerContract;
use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTORegistryContract;
use BAGArt\TelegramBot\Exceptions\TgTechnicalException;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use Illuminate\Cache\CacheManager;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;
use Psr\SimpleCache\CacheInterface;

class TelegramBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Wrappers — pre-initialized from Laravel container
        $this->app->singleton(
            ASKLogWrapper::class,
            function ($app): ASKLogWrapper {
                /** @var Logger $logger */
                $logger = $app->make(Logger::class);
                return new ASKLogWrapper($logger, ASKLogWrapper::LEVEL_INFO);
            }
        );

        $this->app->singleton(
            ASKCacheWrapper::class,
            function ($app): ASKCacheWrapper {
                /** @var CacheManager $cacheManager */
                $cacheManager = $app->make(CacheManager::class);
                $cache = $cacheManager->store();
                return new ASKCacheWrapper($cache);
            }
        );

        $this->app->singleton(
            CacheInterface::class,
            fn () => $this->app->make(ASKCacheWrapper::class),
        );

        // Core factory — builds all registries and creates TgBotSetup instances
        $this->app->singleton(
            TgBotSetupFactory::class,
            fn ($app): TgBotSetupFactory => TgBotSetupFactory::build(
                logger: $app->make(ASKLogWrapper::class),
                cache: $app->make(ASKCacheWrapper::class),
            ),
        );

        // Async socket kernel — shared non-blocking socket multiplexer for fsockopen transport.
        // Configured from tg-outbound-daemon.daemon.socket_pool so the outbound daemon can opt
        // into HTTP/1.1 connection pooling (keep-alive) without touching call sites.
        $this->app->singleton(
            HttpsSocketClient::class,
            function (): HttpsSocketClient {
                $pool = (array) config('tg-outbound-daemon.daemon.socket_pool', []);

                return new HttpsSocketClient(new HttpsSocketClientConfig(
                    keepAlive: (bool) ($pool['enabled'] ?? false),
                    maxIdlePerHost: (int) ($pool['max_idle_per_host'] ?? 4),
                    maxIdleTotal: (int) ($pool['max_idle_total'] ?? 16),
                    idleTimeout: (float) ($pool['idle_timeout'] ?? 30.0),
                ));
            },
        );

        // PoolWarmer — periodic keep-alive connection warmer for the socket pool.
        // Registered as a standalone singleton so the daemon command can inject it
        // into the AsyncKernel's tickable loop independently of the transport.
        $this->app->singleton(
            PoolWarmer::class,
            function (): ?PoolWarmer {
                $pool = (array) config('tg-outbound-daemon.daemon.socket_pool', []);

                if (!($pool['enabled'] ?? false)) {
                    return null;
                }

                $warmCount = (int) ($pool['warm_connections'] ?? 0);
                $warmHost = (string) ($pool['warm_host'] ?? '');

                if ($warmCount <= 0 || $warmHost === '') {
                    return null;
                }

                return new PoolWarmer(
                    client: $this->app->make(HttpsSocketClient::class),
                    warmHost: $warmHost,
                    warmCount: $warmCount,
                    warmInterval: (float) ($pool['warm_interval'] ?? 30.0),
                );
            },
        );

        // Bind individual services from the factory's registries for backward compat
        $this->app->singleton(
            TgApiDTORegistryContract::class,
            TgEntityToDTORegistry::class,
        );

        $this->app->singleton(
            TgRateLimiterContract::class,
            function ($app): ?TgRateLimiterContract {
                $type = config('telegram.rate_limiter');

                return TgRateLimiterRegistry::build()->make(
                    type: $type,
                    cache: $app->make(ASKCacheWrapper::class),
                );
            },
        );

        $this->app->singleton(
            TgRetryPolicyContract::class,
            TgRetryPolicy::class,
        );

        $this->app->singleton(
            TgCircuitBreakerContract::class,
            TgCircuitBreaker::class,
        );

        $this->app->singleton(
            TgBotApiTransportContract::class,
            function ($app): TgBotApiTransportContract {
                return new TgBotApiTransport(
                    httpTransport: $app->make(HttpTransportContract::class),
                );
            },
        );

        $this->app->singleton(
            TgBotApiClientContract::class,
            function ($app): TgBotApiClientContract {
                return new TgBotApiClient(
                    transport: $app->make(TgBotApiTransportContract::class),
                );
            },
        );

        $this->app->singleton(
            TgBotApiDTOClientContract::class,
            function ($app): TgBotApiDTOClientContract {
                return TgBotApiDTOClient::build(
                    transport: $app->make(TgBotApiTransportContract::class),
                );
            },
        );

        $this->app->singleton(
            TgApiDTOMapperContract::class,
            TgApiDTOMapper::class,
        );

        $this->app->singleton(
            TgEntityToDTORegistry::class,
            fn () => TgEntityToDTORegistry::build(),
        );

        $this->app->singleton(
            TgBotsSecretServiceContract::class,
            AutoSecretByTokenService::class,
        );

        $this->app->singleton(
            TgResponseNormalizerContract::class,
            TgResponseNormalizer::class,
        );

        // HttpTransport — selectable via TG_OUTBOUND_TRANSPORT env.
        // Resolves through the ASKClient TransportRegistry so the transport type is a pure
        // configuration concern: "guzzle" / "curl-multi" / "ask-socket". The "ask-socket" variant
        // wraps the singleton HttpsSocketClient (pool-enabled by config above), so daemons that
        // warm the pool get reused connections in every outbound request.
        $this->app->singleton(
            HttpTransportContract::class,
            function ($app): HttpTransportContract {
                $type = (string) config('tg-outbound-daemon.daemon.transport', '');

                if ($type === '') {
                    throw new TgTechnicalException(
                        'TG_OUTBOUND_TRANSPORT is not configured. Publish tg-outbound-daemon.php '
                        .'or set TG_OUTBOUND_TRANSPORT to one of: guzzle, curl-multi, asc-socket.',
                    );
                }

                if ($type === ASKSocketTransport::TYPE) {
                    return new ASKSocketTransport(
                        client: $app->make(HttpsSocketClient::class),
                    );
                }

                return TransportRegistry::build()->make($type);
            },
        );

        // ApiClient — rate-limited, resolver-aware wrapper over the HTTP transport.
        // Owns predictive pacing (ASKRateLimiter) and the promise resolver the async kernel
        // drives; the Telegram adapter sits on top of this, never on the raw transport.
        $this->app->singleton(
            ApiClientContract::class,
            function ($app): ApiClientContract {
                return new ApiClient(
                    transport: $app->make(HttpTransportContract::class),
                    rateLimiter: new ASKRateLimiter(
                        $app->make(ASKCacheWrapper::class),
                        new ASKClock(),
                    ),
                    promiseResolver: new ASKPromiseResolver(),
                );
            },
        );

        // ASKClient — unified execution engine wrapping Telegram transport
        $this->app->singleton(
            ASKClient::class,
            fn ($app): ASKClient => new ASKClient(
                transport: new TgBotApiAskTransport(
                    apiClient: $app->make(ApiClientContract::class),
                    requestFactory: new TgRequestFactory(),
                ),
            ),
        );

        // Registries — built once, shared across all consumers
        $this->app->singleton(
            TypeDTOProcessorRegistry::class,
            fn () => TypeDTOProcessorRegistry::build(),
        );

        // Update processor selector — built once with default config
        $this->app->singleton(
            RegisteredUpdateProcessorSelector::class,
            function ($app): RegisteredUpdateProcessorSelector {
                $factory = $app->make(TgBotSetupFactory::class);

                return new RegisteredUpdateProcessorSelector(
                    serviceConfig: new TgServiceConfig(),
                    botSetup: $factory->create(serviceConfig: new TgServiceConfig()),
                );
            },
        );

        // Webhook parser — fully constructed, ready for controller injection
        $this->app->singleton(
            TgWebhookRequestParser::class,
            function ($app): TgWebhookRequestParser {
                $factory = $app->make(TgBotSetupFactory::class);
                $config = new TgServiceConfig();

                return new TgWebhookRequestParser(
                    tgApiDTOMapper: $factory->dtoMapper($config),
                    selector: $app->make(RegisteredUpdateProcessorSelector::class),
                    secretService: $app->make(TgBotsSecretServiceContract::class),
                    logger: $factory->logger ?? TgBotSetupFactory::createLogger($config),
                    dispatcherRegistry: $factory->dispatcherRegistry,
                );
            },
        );

        $this->registerOutbound();
    }

    public function boot(): void
    {
    }

    /**
     * Bindings for Outbound (Phase 5 — CLI + Daemon).
     *
     * Sender, queue, and stats share the same underlying instances (same queue the
     * sender pushes to, same stats the daemon records). Daemon is NOT registered —
     * it must be built explicitly in CLI commands via `new TgOutboundDaemon(...)`.
     */
    private function registerOutbound(): void
    {
        $this->app->singleton(
            \BAGArt\TelegramBot\Contracts\Outbound\BotTokenResolverContract::class,
            \BAGArt\TelegramBotManagement\Models\TgDbTokenResolver::class,
        );

        $this->app->singleton(
            \BAGArt\TelegramBot\Outbound\TgOutboundStats::class,
            fn ($app): \BAGArt\TelegramBot\Outbound\TgOutboundStats => $app->make(TgBotSetupFactory::class)->createOutboundStats(),
        );

        $this->app->singleton(
            \BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract::class,
            fn ($app): \BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract => $app->make(TgBotSetupFactory::class)->createOutboundSender(),
        );

        $this->app->singleton(
            \BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract::class,
            fn ($app): \BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract => $app->make(TgBotSetupFactory::class)->createOutboundQueue(),
        );
    }
}
