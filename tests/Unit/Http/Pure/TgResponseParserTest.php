<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Http\Pure\TgResponseParser;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Psr\Log\LoggerInterface;

function createResponseTestLogger(): TgBotLogWrapper
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

describe('TgResponseParser', function () {
    describe('buildInternal()', function () {
        it('handles bool type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            $result = $parser->buildInternal($dto, 'bool', true);

            expect($result)->toBeTrue();
        });

        it('handles string type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            $result = $parser->buildInternal($dto, 'string', 'hello');

            expect($result)->toBe('hello');
        });

        it('handles int type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            $result = $parser->buildInternal($dto, 'int', 42);

            expect($result)->toBe(42);
        });

        it('handles mixed type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            $result = $parser->buildInternal($dto, 'mixed', ['key' => 'value']);

            expect($result)->toBe(['key' => 'value']);
        });

        it('handles numeric string for string type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            $result = $parser->buildInternal($dto, 'string', 12345);

            expect($result)->toBe(12345);
        });

        it('handles numeric value for int type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            $result = $parser->buildInternal($dto, 'int', '42');

            expect($result)->toBe('42');
        });

        it('throws exception for invalid bool type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            expect(fn () => $parser->buildInternal($dto, 'bool', 'not-bool'))
                ->toThrow(\TypeError::class);
        });

        it('throws exception for invalid int type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            expect(fn () => $parser->buildInternal($dto, 'int', 'not-int'))
                ->toThrow(\TypeError::class);
        });

        it('throws exception for null type', function () {
            $parser = new TgResponseParser(
                tgApiDTOMapper: Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract::class),
                logger: createResponseTestLogger(),
            );

            $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);
            $dto->shouldReceive('tgApiEntity->name')->andReturn('TestEntity');

            expect(fn () => $parser->buildInternal($dto, 'null', 'value'))
                ->toThrow(\TypeError::class);
        });
    });
});
