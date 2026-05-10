<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgCircuitBreaker;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRateLimiter;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRetryPolicy;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiClient;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Transport\GuzzleTransport;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTORegistryContract;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Illuminate\Cache\CacheManager;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;
use Psr\SimpleCache\CacheInterface;

class TelegramBotServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            TgBotLogWrapper::class,
            function ($app): TgBotLogWrapper {
                /** @var Logger $logger */
                $logger = $app->make(Logger::class);
                TgBotLogWrapper::init($logger, false);

                return TgBotLogWrapper::build();
            }
        );
        $this->app->singleton(
            TgBotCacheWrapper::class,
            function ($app): TgBotCacheWrapper {
                /** @var CacheManager $cacheManager */
                $cacheManager = $app->make(CacheManager::class);
                $cache = $cacheManager->store();
                TgBotCacheWrapper::init($cache);

                return TgBotCacheWrapper::build();
            }
        );
        $this->app->singleton(
            CacheInterface::class,
            fn () => $this->app->make(TgBotCacheWrapper::class),
        );

        $this->app->singleton(
            TgApiDTORegistryContract::class,
            TgEntityToDTORegistry::class,
        );
        $this->app->singleton(
            TgRateLimiterContract::class,
            TgRateLimiter::class,
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
            TgBotApiClientContract::class,
            TgBotApiClient::class,
        );
        $this->app->singleton(
            TgBotApiDTOClientContract::class,
            TgBotApiDTOClient::class,
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
            TgBotApiTransportContract::class,
            fn () => new GuzzleTransport(),
        );
    }

    public function boot(): void
    {
    }
}
