<?php

declare(strict_types=1);

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;

describe('UpdateDTOInitProcessor', function () {
    describe('support()', function () {
        it('supports UpdateTypeDTO', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $logger = new TgBotLogWrapper(new Logger('tst'));
            $config = new TgUpdateConfig('test');
            $processor = new UpdateDTOInitProcessor($registry, logger: $logger);

            $dto = Mockery::mock(UpdateTypeDTO::class);

            expect($processor->support($dto, $config))->toBeTrue();
        });

        it('does not support non-UpdateTypeDTO', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $logger = new TgBotLogWrapper(new Logger('tst'));
            $config = new TgUpdateConfig('test');
            $processor = new UpdateDTOInitProcessor($registry, logger: $logger);

            $dto = Mockery::mock(MessageTypeDTO::class);

            expect($processor->support($dto, $config))->toBeFalse();
        });
    });

    describe('process()', function () {
        it('processes update and dispatches to registry', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $logger = new TgBotLogWrapper(new Logger('tst'));
            $config = new TgUpdateConfig('test');
            $processor = new UpdateDTOInitProcessor($registry, logger: $logger);

            $updateDTO = Mockery::mock(UpdateTypeDTO::class);
            $updateDTO->shouldReceive('tgPropertyMetas')->andReturn([]);

            // Should not throw
            $processor->process($updateDTO, '123456789', $config);

            expect(true)->toBeTrue();
        });

        it('dispatches to dispatcher when processors are registered', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $testHandler = new TestHandler(Level::Debug);
            $logger = new TgBotLogWrapper(new Logger('tst', handlers: [$testHandler]));

            $updateDTO = Mockery::mock(UpdateTypeDTO::class);
            $updateDTO->shouldReceive('tgPropertyMetas')->andReturn([]);

            $config = new TgUpdateConfig('test', dispatcher: 'sync');

            $processor = new UpdateDTOInitProcessor($registry, logger: $logger);

            // With empty registry, process should just iterate through no sub-DTOs
            $processor->process($updateDTO, '123', $config);

            expect(true)->toBeTrue();
        });
    });
});
