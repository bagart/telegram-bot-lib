<?php

use BAGArt\ASKClient\Transporting\HttpTransports\ASKSocketTransport;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\EnvServiceConfigurator;
use BAGArt\TelegramBot\TgBotSetupFactory;

describe('EnvServiceConfigurator', function (): void {
    it('overrides transport when TG_OUTBOUND_TRANSPORT env is set', function (): void {
        $configurator = new EnvServiceConfigurator([], ['TG_OUTBOUND_TRANSPORT' => 'guzzle']);
        $config = $configurator->getServiceConfig();

        expect($config->transport)->toBe('guzzle');
    });

    it('keeps default transport when env is empty', function (): void {
        $configurator = new EnvServiceConfigurator([], ['TG_OUTBOUND_TRANSPORT' => '']);
        $config = $configurator->getServiceConfig();

        expect($config->transport)->toBe(ASKSocketTransport::TYPE);
    });

    it('keeps default transport when env is absent', function (): void {
        $configurator = new EnvServiceConfigurator([], []);
        $config = $configurator->getServiceConfig();

        expect($config->transport)->toBe(ASKSocketTransport::TYPE);
    });

    it('CLI option takes precedence over default but env overrides CLI', function (): void {
        $configurator = new EnvServiceConfigurator(
            ['transport' => 'curl-multi'],
            ['TG_OUTBOUND_TRANSPORT' => 'guzzle'],
        );
        $config = $configurator->getServiceConfig();

        expect($config->transport)->toBe('guzzle');
    });

    it('parses dispatcher from options', function (): void {
        $configurator = new EnvServiceConfigurator(['dispatcher' => 'queue'], []);
        $config = $configurator->getServiceConfig();

        expect($config->dispatcher)->toBe('queue');
    });

    it('parses log level from options', function (): void {
        $configurator = new EnvServiceConfigurator(['log-level' => 'debug'], []);
        $config = $configurator->getServiceConfig();

        expect($config->logLevel)->toBe('debug');
    });
});

describe('TgBotSetupFactory socket pool', function (): void {
    $build = function (TgServiceConfig $config, array $env): ?ASKSocketTransport {
        $method = new ReflectionMethod(TgBotSetupFactory::class, 'buildSocketTransport');

        return $method->invoke(null, $config, new ASKLogWrapper(), $env);
    };

    it('builds an ASKSocketTransport when pool is enabled and transport is ask-socket', function () use ($build): void {
        $config = new TgServiceConfig(transport: ASKSocketTransport::TYPE);
        $env = ['TG_OUTBOUND_SOCKET_POOL' => '1'];

        $transport = $build($config, $env);

        expect($transport)->toBeInstanceOf(ASKSocketTransport::class);
    });

    it('returns null when pool is disabled', function () use ($build): void {
        $config = new TgServiceConfig(transport: ASKSocketTransport::TYPE);
        $env = ['TG_OUTBOUND_SOCKET_POOL' => '0'];

        expect($build($config, $env))->toBeNull();
    });

    it('returns null when pool flag is absent', function () use ($build): void {
        $config = new TgServiceConfig(transport: ASKSocketTransport::TYPE);

        expect($build($config, []))->toBeNull();
    });

    it('returns null when transport is not ask-socket', function () use ($build): void {
        $config = new TgServiceConfig(transport: 'guzzle');
        $env = ['TG_OUTBOUND_SOCKET_POOL' => '1'];

        expect($build($config, $env))->toBeNull();
    });

    it('skips warmup when WARM_CONNECTIONS <= 0', function (): void {
        $transport = new ASKSocketTransport();

        $warmed = TgBotSetupFactory::warmSocketPool(
            $transport,
            new ASKLogWrapper(),
            ['TG_OUTBOUND_WARM_CONNECTIONS' => '0'],
        );

        expect($warmed)->toBe(0);
    });

    it('skips warmup when WARM_HOST is empty', function (): void {
        $transport = new ASKSocketTransport();

        $warmed = TgBotSetupFactory::warmSocketPool(
            $transport,
            new ASKLogWrapper(),
            ['TG_OUTBOUND_WARM_CONNECTIONS' => '4', 'TG_OUTBOUND_WARM_HOST' => ''],
        );

        expect($warmed)->toBe(0);
    });
});
