<?php

declare(strict_types=1);

use BAGArt\TelegramBot\BotServices\BotSecretRegistry;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\Http\Pure\TgWebhookRequestParser;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

describe('TgPureFactory', function () {
    describe('logger()', function () {
        it('returns TgBotLogWrapper instance', function () {
            $logger = TgPureFactory::logger();

            expect($logger)->toBeInstanceOf(TgBotLogWrapper::class);
        });

        it('returns same instance on multiple calls', function () {
            $logger1 = TgPureFactory::logger();
            $logger2 = TgPureFactory::logger();

            expect($logger1)->toBe($logger2);
        });
    });

    describe('cache()', function () {
        it('returns TgBotCacheWrapper instance', function () {
            $cache = TgPureFactory::cache();

            expect($cache)->toBeInstanceOf(TgBotCacheWrapper::class);
        });
    });

    describe('botSecretRegistry()', function () {
        it('returns BotSecretRegistry instance', function () {
            $registry = TgPureFactory::botSecretRegistry();

            expect($registry)->toBeInstanceOf(BotSecretRegistry::class);
        });

        it('returns new instance on each call', function () {
            $registry1 = TgPureFactory::botSecretRegistry();
            $registry2 = TgPureFactory::botSecretRegistry();

            expect($registry1)->not->toBe($registry2);
        });
    });

    describe('webhook()', function () {
        it('returns TgWebhookRequestParser instance', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $webhook = TgPureFactory::webhook($registry);

            expect($webhook)->toBeInstanceOf(TgWebhookRequestParser::class);
        });
    });
});
