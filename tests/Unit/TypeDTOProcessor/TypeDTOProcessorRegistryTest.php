<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;

describe('TypeDTOProcessorRegistry', function () {
    $botSetup = TgBotSetupFactory::build()->create(serviceConfig: new TgServiceConfig());

    describe('register()', function () use ($botSetup) {
        it('registers a processor', function () use ($botSetup) {
            $registry = TypeDTOProcessorRegistry::build();
            $processor = Mockery::mock(TgTypeDTOProcessorContract::class);

            $result = $registry->register(UpdateTypeDTO::class, $processor);

            expect($result)->toBe($registry);
        });

        it('registers multiple processors for same DTO', function () use ($botSetup) {
            $registry = TypeDTOProcessorRegistry::build();
            $processor1 = Mockery::mock(TgTypeDTOProcessorContract::class, 'ProcessorMock1');
            $processor2 = Mockery::mock(TgTypeDTOProcessorContract::class, 'ProcessorMock2');

            $registry->register(UpdateTypeDTO::class, $processor1);
            $registry->register(UpdateTypeDTO::class, $processor2);

            $processors = iterator_to_array($registry->get(UpdateTypeDTO::class, new TgServiceConfig('test'), $botSetup));

            expect($processors)->toHaveCount(2);
        });
    });

    describe('get()', function () use ($botSetup) {
        it('returns empty generator for unregistered DTO', function () use ($botSetup) {
            $registry = TypeDTOProcessorRegistry::build();

            $processors = iterator_to_array($registry->get('NonExistentDTO', new TgServiceConfig('test'), $botSetup));

            expect($processors)->toBeEmpty();
        });

        it('returns registered processor', function () use ($botSetup) {
            $registry = TypeDTOProcessorRegistry::build();
            $processor = Mockery::mock(TgTypeDTOProcessorContract::class);

            $registry->register('TestDTO', $processor);

            $processors = iterator_to_array($registry->get('TestDTO', new TgServiceConfig('test'), $botSetup));

            expect($processors)->toHaveCount(1);
            expect($processors[0])->toBe($processor);
        });

        it('instantiates string processor classes', function () use ($botSetup) {
            $registry = TypeDTOProcessorRegistry::build();
            $registry->check = false;

            $registry->register('TestDTO', Mockery::mock(TgTypeDTOProcessorContract::class)::class);

            $thrown = false;
            try {
                iterator_to_array($registry->get('TestDTO', new TgServiceConfig('test'), $botSetup));
            } catch (\Throwable) {
                $thrown = true;
            }

            expect($thrown)->toBeTrue();
        });
    });
});
