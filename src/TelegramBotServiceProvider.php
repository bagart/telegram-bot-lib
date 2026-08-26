<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\ASKClient\Client\ASKClient;
use BAGArt\ASKClient\Contracts\Client\ApiClientContract;
use BAGArt\ASKClient\Contracts\Dns\AskDnsAdapterContract;
use BAGArt\ASKClient\Contracts\Transport\HttpTransportContract;
use BAGArt\ASKClient\Dns\AskDnsConfigFactory;
use BAGArt\ASKClient\Dns\AskDnsFactory;
use BAGArt\ASKClient\Dns\AskDnsRegistry;
use BAGArt\ASKClient\HttpClient\Adapters\AskCurlMultiClientAdapter;
use BAGArt\ASKClient\HttpClient\Adapters\AskGuzzleClientAdapter;
use BAGArt\ASKClient\HttpClient\ApiClient;
use BAGArt\ASKClient\HttpClient\PoolWarmer;
use BAGArt\ASKClient\RateLimiter\ASKRateLimiter;
use BAGArt\ASKClient\SocketClient\AskHttpSocketClient;
use BAGArt\ASKClient\SocketClient\HttpsSocketClientConfig;
use BAGArt\ASKClient\Transport\Adapters\ASKSocketTransportAdapter;
use BAGArt\ASKClient\Transport\Adapters\CurlMultiTransportAdapter;
use BAGArt\ASKClient\Transport\Adapters\GuzzleTransportAdapter;
use BAGArt\ASKClient\Transport\HttpTransportRegistry;
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
use BAGArt\TelegramBot\Contracts\Modules\CommandRouteContract;
use BAGArt\TelegramBot\Contracts\Modules\ModuleEnablementContract;
use BAGArt\TelegramBot\Contracts\Outbound\BotTokenResolverContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTORegistryContract;
use BAGArt\TelegramBot\Exceptions\TgTechnicalException;
use BAGArt\TelegramBot\Framework\Laravel\LaravelAskDnsResolver;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\Modules\AttributedComponentsScanner;
use BAGArt\TelegramBot\Modules\ModuleBootloader;
use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\Modules\TgModuleRegistrar;
use BAGArt\TelegramBot\Modules\TgModuleRegistry;
use BAGArt\TelegramBot\Modules\TgWebApiRegistry;
use BAGArt\TelegramBot\Modules\TgWebPermissionRegistry;
use BAGArt\TelegramBot\Modules\TgWebResourceRegistry;
use BAGArt\TelegramBot\Modules\TgWebUiRegistry;
use BAGArt\TelegramBot\Modules\TypedModuleRegistrar;
use BAGArt\TelegramBot\Outbound\OutboundMiddlewareRegistry;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRuleRegistry;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use BAGArt\TelegramBotManagement\Models\TgDbTokenResolver;
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

        // Core factory — builds all registries and creates TgBotSetup instances.
        // Shares the container's TypeDTOProcessorRegistry so processors
        // registered by bootloaded modules are visible to every TgBotSetup.
        $this->app->singleton(
            TgBotSetupFactory::class,
            fn ($app): TgBotSetupFactory => TgBotSetupFactory::build(
                logger: $app->make(ASKLogWrapper::class),
                cache: $app->make(ASKCacheWrapper::class),
                processorRegistry: $app->make(TypeDTOProcessorRegistry::class),
                messageRules: $app->make(MessageValidationRuleRegistry::class),
                outboundMiddlewares: $app->make(OutboundMiddlewareRegistry::class),
                commandRegistry: $app->make(TgCommandRegistry::class),
            ),
        );

        // Pluggable DNS adapter — shared singleton so all transports reuse one
        // in-process DNS cache (positive + negative) and one set of outbound query
        // sockets. `adapter=null` keeps the per-transport default; this singleton
        // feeds only the ask-socket client, so null → 'ask-dns' (DEFAULT_TYPE) is
        // the correct per-transport mapping here. curl/guzzle transports with
        // adapter=null never touch this singleton (libcurl resolver).
        $this->app->singleton(
            AskDnsAdapterContract::class,
            function ($app): AskDnsAdapterContract {
                $dns = (array) config('tg-outbound-daemon.daemon.dns', []);
                $config = AskDnsConfigFactory::fromLaravelConfig($dns);

                return (new AskDnsRegistry(
                    logger: $app->make(ASKLogWrapper::class),
                    factory: new AskDnsFactory(
                        resolver: new LaravelAskDnsResolver($app),
                    ),
                ))->make($dns['adapter'] ?? null, $config);
            },
        );

        // Async socket kernel — shared non-blocking socket multiplexer for fsockopen transport.
        // Configured from tg-outbound-daemon.daemon.socket_pool so the outbound daemon can opt
        // into HTTP/1.1 connection pooling (keep-alive) without touching call sites.
        $this->app->singleton(
            AskHttpSocketClient::class,
            function (): AskHttpSocketClient {
                $pool = (array) config('tg-outbound-daemon.daemon.socket_pool', []);

                return new AskHttpSocketClient(
                    new HttpsSocketClientConfig(
                        keepAlive: (bool) ($pool['enabled'] ?? false),
                        maxIdlePerHost: (int) ($pool['max_idle_per_host'] ?? 4),
                        maxIdleTotal: (int) ($pool['max_idle_total'] ?? 16),
                        idleTimeout: (float) ($pool['idle_timeout'] ?? 30.0),
                    ),
                    null,
                    $this->app->make(AskDnsAdapterContract::class),
                );
            },
        );

        // PoolWarmer — periodic keep-alive connection warmer for the socket pool.
        // Registered as a standalone singleton so the daemon command can inject it
        // into the AsyncKernel's tickable loop independently of the transport.
        $this->app->singleton(
            PoolWarmer::class,
            function (): ?PoolWarmer {
                $pool = (array) config('tg-outbound-daemon.daemon.socket_pool', []);

                if (! ($pool['enabled'] ?? false)) {
                    return null;
                }

                $warmCount = (int) ($pool['warm_connections'] ?? 0);
                $warmHost = (string) ($pool['warm_host'] ?? '');

                if ($warmCount <= 0 || $warmHost === '') {
                    return null;
                }

                return new PoolWarmer(
                    client: $this->app->make(AskHttpSocketClient::class),
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
        // Resolves through the ASKClient HttpTransportRegistry so the transport type is a pure
        // configuration concern: "guzzle" / "curl-multi" / "ask-socket". The "ask-socket" variant
        // wraps the singleton AskHttpSocketClient (pool-enabled by config above), so daemons that
        // warm the pool get reused connections in every outbound request.
        //
        // When a custom DNS adapter is configured (TG_DNS_ADAPTER), curl-multi/guzzle transports
        // receive a DNS-aware client adapter so libcurl honors CURLOPT_DNS_SERVERS (requires the
        // c-ares libcurl build; otherwise the server list is ignored with a warning).
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

                if ($type === ASKSocketTransportAdapter::TYPE) {
                    return new ASKSocketTransportAdapter(
                        client: $app->make(AskHttpSocketClient::class),
                    );
                }

                $dnsConfig = $this->needsDnsServers()
                    ? AskDnsConfigFactory::fromLaravelConfig((array) config('tg-outbound-daemon.daemon.dns', []))
                    : null;

                return match ($type) {
                    CurlMultiTransportAdapter::TYPE => new CurlMultiTransportAdapter(
                        client: new AskCurlMultiClientAdapter(dnsConfig: $dnsConfig),
                    ),
                    GuzzleTransportAdapter::TYPE => new GuzzleTransportAdapter(
                        client: new AskGuzzleClientAdapter(dnsConfig: $dnsConfig),
                    ),
                    default => HttpTransportRegistry::build()->make($type),
                };
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

        // Registries — built once, shared across all consumers.
        // Core processors (MessageValidatorProcessor) are registered
        // unconditionally; module processors are appended on boot.
        $this->app->singleton(
            TypeDTOProcessorRegistry::class,
            fn (): TypeDTOProcessorRegistry => TgBotSetupFactory::processorRegistry(),
        );

        // Shared validation-rule registry: core rules registered by default,
        // module rules appended by bootloaded modules via the registrar
        $this->app->singleton(
            MessageValidationRuleRegistry::class,
            fn (): MessageValidationRuleRegistry => MessageValidationRuleRegistry::withCoreRules(),
        );

        // Plugin foundation — module registry, registrar and bootloader
        $this->app->singleton(TgModuleRegistry::class);
        $this->app->singleton(OutboundMiddlewareRegistry::class);
        $this->app->singleton(TgCommandRegistry::class);
        $this->app->singleton(TgWebUiRegistry::class);
        $this->app->singleton(TgWebApiRegistry::class);
        $this->app->singleton(TgWebResourceRegistry::class);
        $this->app->singleton(TgWebPermissionRegistry::class);
        $this->app->singleton(
            AttributedComponentsScanner::class,
            fn ($app): AttributedComponentsScanner => new AttributedComponentsScanner(
                $app->make(ASKCacheWrapper::class),
            ),
        );
        $this->app->singleton(
            TgModuleRegistrar::class,
            fn ($app): TgModuleRegistrar => new TypedModuleRegistrar(
                $app->make(TypeDTOProcessorRegistry::class),
                $app->make(MessageValidationRuleRegistry::class),
                $app->make(OutboundMiddlewareRegistry::class),
                $app->make(TgCommandRegistry::class),
                $app->make(AttributedComponentsScanner::class),
            ),
        );
        $this->app->singleton(
            ModuleBootloader::class,
            fn ($app): ModuleBootloader => new ModuleBootloader(
                registrar: $app->make(TgModuleRegistrar::class),
                registry: $app->make(TgModuleRegistry::class),
                logger: $app->make(ASKLogWrapper::class),
                webUiRegistry: $app->make(TgWebUiRegistry::class),
                webApiRegistry: $app->make(TgWebApiRegistry::class),
                webResourceRegistry: $app->make(TgWebResourceRegistry::class),
                webPermissionRegistry: $app->make(TgWebPermissionRegistry::class),
            ),
        );

        // Update processor selector — built once with default config.
        // Module enablement is injected only when a management layer binding
        // exists (the lib itself stays enablement-agnostic).
        $this->app->singleton(
            RegisteredUpdateProcessorSelector::class,
            function ($app): RegisteredUpdateProcessorSelector {
                $factory = $app->make(TgBotSetupFactory::class);

                return new RegisteredUpdateProcessorSelector(
                    serviceConfig: new TgServiceConfig(),
                    botSetup: $factory->create(serviceConfig: new TgServiceConfig()),
                    moduleEnablement: $app->bound(ModuleEnablementContract::class)
                        ? $app->make(ModuleEnablementContract::class)
                        : null,
                    commandRoutes: $app->bound(CommandRouteContract::class)
                        ? $app->make(CommandRouteContract::class)
                        : null,
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
        $this->bootModules();
    }

    /**
     * Discovery: local folders (config('telegram.modules') scan) and
     * composer-installed providers (config('telegram.modules_providers'))
     * both resolve to TgModuleContract class-strings and are booted through
     * the ModuleBootloader with per-module fault isolation.
     *
     * Deprecated: when the Telegram Module Engine is installed its
     * config/tg_modules.php makes config('tg_modules') non-null — module
     * booting is then owned by TelegramModuleEngineServiceProvider, which
     * consumes the engine registry plus these keys as deprecated aliases.
     */
    private function bootModules(): void
    {
        if (config('tg_modules') !== null) {
            $this->logDeprecatedModuleSources();

            return;
        }

        $providers = [];

        foreach ((array) config('telegram.modules', []) as $moduleConfig) {
            if (is_array($moduleConfig)
                && isset($moduleConfig['provider'])
                && is_string($moduleConfig['provider'])
            ) {
                $providers[] = $moduleConfig['provider'];
            }
        }

        foreach ((array) config('telegram.modules_providers', []) as $providerClass) {
            if (is_string($providerClass)) {
                $providers[] = $providerClass;
            }
        }

        if ($providers === []) {
            return;
        }

        /** @var ModuleBootloader $bootloader */
        $bootloader = $this->app->make(ModuleBootloader::class);
        $bootloader->bootAll(array_values(array_unique($providers)));
    }

    /**
     * One warning per boot when the engine owns module booting but legacy
     * telegram.modules* sources still contribute entries (migration aid).
     *
     * @param  array<int, string>  $legacyProviders  class-strings found in the deprecated keys
     */
    private function logDeprecatedModuleSources(): void
    {
        $legacy = [];

        foreach ((array) config('telegram.modules', []) as $moduleConfig) {
            if (is_array($moduleConfig)
                && isset($moduleConfig['provider'])
                && is_string($moduleConfig['provider'])
            ) {
                $legacy[] = $moduleConfig['provider'];
            }
        }

        foreach ((array) config('telegram.modules_providers', []) as $providerClass) {
            if (is_string($providerClass)) {
                $legacy[] = $providerClass;
            }
        }

        if ($legacy === []) {
            return;
        }

        logger()->warning('tg-modules: legacy telegram.modules/modules_providers sources are deprecated, declare modules in config/tg_modules.php', [
            'providers' => array_values(array_unique($legacy)),
        ]);
    }

    /**
     * True when the DNS config pins custom upstream servers that libcurl should
     * use via CURLOPT_DNS_SERVERS. Returns false for the null/default case so the
     * curl/guzzle transports keep their zero-arg default client adapters.
     */
    private function needsDnsServers(): bool
    {
        $config = AskDnsConfigFactory::fromLaravelConfig(
            (array) config('tg-outbound-daemon.daemon.dns', []),
        );

        return $config->dnsServers() !== [];
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
            BotTokenResolverContract::class,
            TgDbTokenResolver::class,
        );

        $this->app->singleton(
            TgOutboundStats::class,
            fn ($app): TgOutboundStats => $app->make(
                TgBotSetupFactory::class
            )->createOutboundStats(),
        );

        $this->app->singleton(
            TgSenderContract::class,
            fn ($app): TgSenderContract => $app->make(
                TgBotSetupFactory::class
            )->createOutboundSender(),
        );

        $this->app->singleton(
            OutboundQueueContract::class,
            fn ($app): OutboundQueueContract => $app->make(
                TgBotSetupFactory::class
            )->createOutboundQueue(),
        );
    }
}
