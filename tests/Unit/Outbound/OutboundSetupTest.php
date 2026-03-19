<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\Ordering\DefaultOrderingStrategy;
use BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\TgSender;
use BAGArt\TelegramBot\TgBotSetupFactory;

if (!class_exists('ControllableClock') || !function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../Helpers.php';
}

describe('OutboundSetup', function () {
    it('createOutboundDaemonParts returns queue, pipeline, circuitBreaker, stats and leaseRenewer', function () {
        $factory = TgBotSetupFactory::build();
        $parts = $factory->createOutboundDaemonParts(workerConfig: new OutboundWorkerConfig());

        expect($parts['queue'])->toBeInstanceOf(OutboundQueueContract::class)
            ->and($parts['pipeline'])->toBeInstanceOf(OutboundPipeline::class)
            ->and($parts['circuitBreaker'])->toBeInstanceOf(OutboundCircuitBreaker::class)
            ->and($parts['stats'])->toBeInstanceOf(BAGArt\TelegramBot\Outbound\TgOutboundStats::class)
            ->and($parts['leaseRenewer'])->toBeInstanceOf(BAGArt\TelegramBot\Outbound\LeaseRenewer::class);
    });

    it('createOutboundQueue returns a queue', function () {
        $factory = TgBotSetupFactory::build();
        $queue = $factory->createOutboundQueue();

        expect($queue)->toBeInstanceOf(OutboundQueueContract::class);
    });

    it('createOutboundStats returns stats', function () {
        $factory = TgBotSetupFactory::build();
        $stats = $factory->createOutboundStats();

        expect($stats)->toBeInstanceOf(BAGArt\TelegramBot\Outbound\TgOutboundStats::class);
    });

    it('createOutboundSender returns a valid sender', function () {
        $factory = TgBotSetupFactory::build();
        $sender = $factory->createOutboundSender();

        expect($sender)->toBeInstanceOf(TgSenderContract::class);
    });

    it('sender pushes to the same queue the daemon reads from', function () {
        $factory = TgBotSetupFactory::build();
        $workerConfig = new OutboundWorkerConfig();
        $parts = $factory->createOutboundDaemonParts(workerConfig: $workerConfig);

        $sender = new TgSender($parts['queue'], $factory->dtoMapper(new \BAGArt\TelegramBot\Configs\TgServiceConfig()), new DefaultOrderingStrategy());

        $botConfig = new \BAGArt\TelegramBot\Configs\TgBotConfig(token: 'test:token', botId: 'sender_test');
        $dto = new class () implements \BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract {
            public static function getReturnTypes(): array
            {
                return [];
            }
            public static function tgApiEntity(): \BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract
            {
                throw new \RuntimeException('not used');
            }
            public static function tgEntityScope(): \BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract
            {
                throw new \RuntimeException('not used');
            }
            public static function tgPropertyMetas(): array
            {
                return [];
            }
        };

        $sender->send($botConfig, $dto);
        expect($parts['queue']->size())->toBe(1);
    });
});
