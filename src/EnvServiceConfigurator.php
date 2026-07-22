<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\LaravelQueueDispatcher\LaravelProcessingDispatcher;

/**
 * Parses CLI options and Environment variables into a clean TgServiceConfig DTO.
 */
final readonly class EnvServiceConfigurator
{
    private TgServiceConfig $serviceConfig;

    public function __construct(array $options = [], array $env = [])
    {
        $env = $env ?: $_ENV ?: getenv();
        $config = new TgServiceConfig();

        if (isset($options['dispatcher'])) {
            $config->dispatcher = $options['dispatcher'];
        } elseif (isset($options['queue'])) {
            $config->dispatcher = LaravelProcessingDispatcher::TYPE;
        }

        $config->logLevel = $options['log-level'] ?? ASKLogWrapper::LEVEL_INFO;

        if (isset($options['cache-driver'])) {
            $config->cacheDriver = $options['cache-driver'];
        }

        $envTransport = $env['TG_OUTBOUND_TRANSPORT'] ?? null;
        $config->transport = (!empty($envTransport))
            ? $envTransport
            : ($options['transport'] ?? $config->transport);

        $envDns = $env['TG_DNS_ADAPTER'] ?? null;
        $config->dns = (!empty($envDns))
            ? $envDns
            : ($options['dns'] ?? $config->dns);

        $this->serviceConfig = $config;
    }

    public function getServiceConfig(): TgServiceConfig
    {
        return $this->serviceConfig;
    }
}
