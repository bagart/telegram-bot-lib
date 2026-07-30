<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\Processing\Processors\DbgDTOToLoggerProcessor;
use BAGArt\TelegramBot\Processing\Processors\DbgDTOToStdProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageDTOShowToConsoleProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageDTOToDbProcessor;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidatorProcessor;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;

describe('TypeDTOProcessorRegistry', function () {
    $botSetup = TgBotSetupFactory::build()->create(serviceConfig: new TgServiceConfig());
    $context = BotProcessorContext::fromBotSetup($botSetup);

    describe('register()', function () use ($context) {
        it('registers a processor', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $processor = Mockery::mock(TgTypeDTOProcessorContract::class);

            $result = $registry->register(UpdateTypeDTO::class, $processor);

            expect($result)->toBe($registry);
        });

        it('registers multiple processors for same DTO', function () use ($context) {
            $registry = TypeDTOProcessorRegistry::build();
            $processor1 = Mockery::mock(TgTypeDTOProcessorContract::class, 'ProcessorMock1');
            $processor2 = Mockery::mock(TgTypeDTOProcessorContract::class, 'ProcessorMock2');

            $registry->register(UpdateTypeDTO::class, $processor1);
            $registry->register(UpdateTypeDTO::class, $processor2);

            $processors = iterator_to_array(
                $registry->get(UpdateTypeDTO::class, $context)
            );

            expect($processors)->toHaveCount(2);
        });
    });

    describe('get()', function () use ($context) {
        it('returns empty generator for unregistered DTO', function () use ($context) {
            $registry = TypeDTOProcessorRegistry::build();

            $processors = iterator_to_array($registry->get('NonExistentDTO', $context));

            expect($processors)->toBeEmpty();
        });

        it('returns registered processor', function () use ($context) {
            $registry = TypeDTOProcessorRegistry::build();
            $processor = Mockery::mock(TgTypeDTOProcessorContract::class);

            $registry->register('TestDTO', $processor);

            $processors = iterator_to_array($registry->get('TestDTO', $context));

            expect($processors)->toHaveCount(1);
            expect($processors[0])->toBe($processor);
        });

        it('deduplicates the same processor class for the same DTO', function () use ($context) {
            $registry = TypeDTOProcessorRegistry::build();

            $registry->register(UpdateTypeDTO::class, DbgDTOToLoggerProcessor::class);
            $registry->register(UpdateTypeDTO::class, DbgDTOToLoggerProcessor::class);

            $processors = iterator_to_array($registry->get(UpdateTypeDTO::class, $context));

            expect($processors)->toHaveCount(1);
        });
    });

    describe('get() with real processors (AC-0: contract drift fix)', function () use ($context) {
        it('builds real processors through BotProcessorContext without TypeError', function () use ($context) {
            $registry = TgBotSetupFactory::processorRegistry(new ProcessorConfig(
                echo: false,
                show: true,
                log: true,
                store: true,
                dbg: true,
            ));

            $built = [];
            foreach ($registry->get(MessageTypeDTO::class, $context) as $processor) {
                $built[] = $processor::class;
            }

            expect($built)->toContain(DbgDTOToLoggerProcessor::class);
            expect($built)->toContain(DbgDTOToStdProcessor::class);
            expect($built)->toContain(MessageDTOShowToConsoleProcessor::class);
            expect($built)->toContain(MessageDTOToDbProcessor::class);
            expect($built)->toContain(MessageValidatorProcessor::class);
            expect($built)->each->toImplement(TgTypeDTOProcessorContract::class);
        });

        it('registers core MessageValidatorProcessor without any config (webhook path)', function () use ($context) {
            $registry = TgBotSetupFactory::processorRegistry();

            $built = [];
            foreach ($registry->get(MessageTypeDTO::class, $context) as $processor) {
                $built[] = $processor::class;
            }

            expect($built)->toContain(MessageValidatorProcessor::class);
            expect($built)->not->toContain(DbgDTOToLoggerProcessor::class);
        });
    });
});
