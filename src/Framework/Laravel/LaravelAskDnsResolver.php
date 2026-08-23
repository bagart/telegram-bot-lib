<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Framework\Laravel;

use BAGArt\ASKClient\Contracts\Dns\AskDnsAdapterContract;
use BAGArt\ASKClient\Contracts\Dns\AskDnsResolverContract;
use BAGArt\ASKClient\Dns\AskDnsConfig;
use Illuminate\Contracts\Container\Container;

/**
 * Container bridge for {@see AskDnsResolverContract}: resolves DNS adapter
 * classes through the Laravel container so adapters may receive DI services
 * alongside their config.
 */
final class LaravelAskDnsResolver implements AskDnsResolverContract
{
    public function __construct(
        private readonly Container $app,
    ) {
    }

    public function resolve(string $class, AskDnsConfig $config): AskDnsAdapterContract
    {
        return $this->app->make($class, ['config' => $config]);
    }
}
