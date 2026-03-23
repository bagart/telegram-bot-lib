<?php

declare(strict_types=1);

use BAGArt\TelegramBot\BotServices\BotSecretDTO;
use BAGArt\TelegramBot\BotServices\BotSecretRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Psr\Log\LoggerInterface;

function createLogger(): TgBotLogWrapper
{
    return new TgBotLogWrapper(
        logger: new class implements LoggerInterface {
            public function log($level, string|\Stringable $message, array $context = []): void {}
            public function emergency(string|\Stringable $message, array $context = []): void {}
            public function alert(string|\Stringable $message, array $context = []): void {}
            public function critical(string|\Stringable $message, array $context = []): void {}
            public function error(string|\Stringable $message, array $context = []): void {}
            public function warning(string|\Stringable $message, array $context = []): void {}
            public function notice(string|\Stringable $message, array $context = []): void {}
            public function info(string|\Stringable $message, array $context = []): void {}
            public function debug(string|\Stringable $message, array $context = []): void {}
        }
    );
}

describe('BotSecretRegistry', function () {
    $logger = createLogger();

    describe('register()', function () use ($logger) {
        it('registers a bot', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);
            $bot = new BotSecretDTO(token: '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');

            $result = $registry->register($bot);

            expect($result)->toBe($registry);
            expect($registry->has('123456789'))->toBeTrue();
        });

        it('allows overwriting existing bot', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);
            $bot1 = new BotSecretDTO(token: '123456789:AAAaaaBBBbbbCCCccc');
            $bot2 = new BotSecretDTO(token: '123456789:DDDdddEEEeeeFFFfff');

            $registry->register($bot1);
            $registry->register($bot2);

            expect($registry->getBot('123456789'))->toBe($bot2);
        });
    });

    describe('getBot()', function () use ($logger) {
        it('returns bot by id', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);
            $bot = new BotSecretDTO(token: '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');

            $registry->register($bot);

            expect($registry->getBot('123456789'))->toBe($bot);
        });

        it('returns null for non-existent bot', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);

            expect($registry->getBot('999999999'))->toBeNull();
        });
    });

    describe('getBotCount()', function () use ($logger) {
        it('returns 0 for empty registry', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);

            expect($registry->getBotCount())->toBe(0);
        });

        it('returns correct count', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);

            $registry->register(new BotSecretDTO(token: '111111111:AAAaaaBBBbbbCCCccc'));
            $registry->register(new BotSecretDTO(token: '222222222:DDDdddEEEeeeFFFfff'));

            expect($registry->getBotCount())->toBe(2);
        });
    });

    describe('has()', function () use ($logger) {
        it('returns true for registered bot', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);
            $registry->register(new BotSecretDTO(token: '123456789:ABCdefGHIjklMNOpqrsTUVwxyz'));

            expect($registry->has('123456789'))->toBeTrue();
        });

        it('returns false for non-existent bot', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);

            expect($registry->has('999999999'))->toBeFalse();
        });
    });

    describe('getBotsBySecret()', function () use ($logger) {
        it('returns bots by secret', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);
            $bot1 = new BotSecretDTO(token: '111111111:AAAaaaBBBbbbCCCccc', secret: 'secret1');
            $bot2 = new BotSecretDTO(token: '222222222:DDDdddEEEeeeFFFfff', secret: 'secret1');

            $registry->register($bot1);
            $registry->register($bot2);

            $bots = iterator_to_array($registry->getBotsBySecret('secret1'));

            expect($bots)->toHaveCount(2);
            expect($bots)->toContain($bot1);
            expect($bots)->toContain($bot2);
        });

        it('returns empty generator for non-existent secret', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);

            $bots = iterator_to_array($registry->getBotsBySecret('non-existent'));

            expect($bots)->toBeEmpty();
        });
    });

    describe('getBotIdsBySecret()', function () use ($logger) {
        it('returns bot ids by secret', function () use ($logger) {
            $registry = new BotSecretRegistry($logger);
            $registry->register(new BotSecretDTO(token: '111111111:AAAaaaBBBbbbCCCccc', secret: 'secret1'));
            $registry->register(new BotSecretDTO(token: '222222222:DDDdddEEEeeeFFFfff', secret: 'secret1'));

            $ids = iterator_to_array($registry->getBotIdsBySecret('secret1'));

            expect($ids)->toHaveCount(2);
            expect($ids)->toContain('111111111');
            expect($ids)->toContain('222222222');
        });
    });
});
