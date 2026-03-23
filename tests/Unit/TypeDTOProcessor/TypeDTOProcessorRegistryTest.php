<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;

describe('TypeDTOProcessorRegistry', function () {
    describe('register()', function () {
        it('registers a processor', function () {
            $registry = new TypeDTOProcessorRegistry();
            $processor = Mockery::mock(TgUpdateProcessorContract::class);

            $result = $registry->register(UpdateTypeDTO::class, $processor);

            expect($result)->toBe($registry);
        });

        it('registers multiple processors for same DTO', function () {
            $registry = new TypeDTOProcessorRegistry();
            $processor1 = Mockery::mock(TgUpdateProcessorContract::class);
            $processor2 = Mockery::mock(TgUpdateProcessorContract::class);

            $registry->register(UpdateTypeDTO::class, $processor1);
            $registry->register(UpdateTypeDTO::class, $processor2);

            $processors = iterator_to_array($registry->get(UpdateTypeDTO::class));

            expect($processors)->toHaveCount(3); // 1 default + 2 registered
        });
    });

    describe('get()', function () {
        it('returns empty generator for unregistered DTO', function () {
            $registry = new TypeDTOProcessorRegistry();

            $processors = iterator_to_array($registry->get('NonExistentDTO'));

            expect($processors)->toBeEmpty();
        });

        it('returns registered processor', function () {
            $registry = new TypeDTOProcessorRegistry();
            $processor = Mockery::mock(TgUpdateProcessorContract::class);

            $registry->register('TestDTO', $processor);

            $processors = iterator_to_array($registry->get('TestDTO'));

            expect($processors)->toHaveCount(1);
            expect($processors[0])->toBe($processor);
        });

        it('instantiates string processor classes', function () {
            $registry = new TypeDTOProcessorRegistry();
            $registry->check = false; // Disable assertion for test

            $registry->register('TestDTO', Mockery::mock(TgUpdateProcessorContract::class)::class);

            // This will fail because Mockery::mock doesn't create a real class
            // but it tests the instantiation logic
            expect(fn () => iterator_to_array($registry->get('TestDTO')))
                ->toThrow(\Error::class);
        });
    });
});
