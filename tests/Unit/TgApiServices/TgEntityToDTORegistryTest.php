<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Exceptions\TgUnregisteredEntityNameException;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Psr\Log\LoggerInterface;

function createRegistryLogger(): TgBotLogWrapper
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

describe('TgEntityToDTORegistry', function () {
    describe('register()', function () {
        it('registers a DTO class', function () {
            $registry = new TgEntityToDTORegistry(createRegistryLogger());

            $result = $registry->register(UserTypeDTO::class);

            expect($result)->toBe($registry);
        });

        it('allows overwriting by default', function () {
            $registry = new TgEntityToDTORegistry(createRegistryLogger());

            $registry->register(UserTypeDTO::class);
            $registry->register(UserTypeDTO::class);

            expect($registry->getDTO('User'))->toBe(UserTypeDTO::class);
        });
    });

    describe('getDTO()', function () {
        it('returns registered DTO class', function () {
            $registry = new TgEntityToDTORegistry(createRegistryLogger());
            $registry->register(UserTypeDTO::class);

            expect($registry->getDTO('User'))->toBe(UserTypeDTO::class);
        });

        it('throws exception for unregistered entity', function () {
            $registry = new TgEntityToDTORegistry(createRegistryLogger());

            expect(fn () => $registry->getDTO('NonExistent'))
                ->toThrow(TgUnregisteredEntityNameException::class);
        });

        it('returns DTO with entity scope', function () {
            $registry = new TgEntityToDTORegistry(createRegistryLogger());
            $registry->register(UserTypeDTO::class);

            expect($registry->getDTO('User', TgApiEntityScopeEnum::Type))->toBe(UserTypeDTO::class);
        });

        it('throws exception for wrong entity scope', function () {
            $registry = new TgEntityToDTORegistry(createRegistryLogger());
            $registry->register(UserTypeDTO::class);

            expect(fn () => $registry->getDTO('User', TgApiEntityScopeEnum::Method))
                ->toThrow(TgUnregisteredEntityNameException::class);
        });
    });
});
