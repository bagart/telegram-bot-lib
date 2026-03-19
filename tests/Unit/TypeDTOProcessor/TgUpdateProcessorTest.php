<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\Enum\ChatPropTypeEnum;

function createTestSelectorWithRegistry(TypeDTOProcessorRegistry $registry): RegisteredUpdateProcessorSelector
{
    $factory = TgBotSetupFactory::build();
    $setup = $factory->create(
        serviceConfig: new TgServiceConfig(),
        processorRegistryOverride: $registry,
    );

    return new RegisteredUpdateProcessorSelector(
        serviceConfig: new TgServiceConfig(),
        botSetup: $setup,
    );
}

describe('RegisteredUpdateProcessorSelector', function () {
    describe('selectProcessors()', function () {
        it('returns empty generator for update with no populated properties', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $selector = createTestSelectorWithRegistry($registry);

            $updateDTO = new UpdateTypeDTO(updateId: 1);

            $botConfig = new TgBotConfig(token: '123456789:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

            $selections = iterator_to_array($selector->selectProcessors($updateDTO, $botConfig));

            expect($selections)->toBe([]);
        });

        it('returns processor for UpdateTypeDTO message property when registered', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $processor = Mockery::mock(\BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract::class);
            $processor->shouldReceive('support')->andReturn(true);

            $registry->register(MessageTypeDTO::class, $processor);

            $selector = createTestSelectorWithRegistry($registry);

            $chat = new ChatTypeDTO(id: '1', type: ChatPropTypeEnum::PRIVATE);
            $message = new MessageTypeDTO(messageId: 1, date: 1000000, chat: $chat);
            $updateDTO = new UpdateTypeDTO(updateId: 1, message: $message);

            $botConfig = new TgBotConfig(token: '123456789:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

            $selections = iterator_to_array($selector->selectProcessors($updateDTO, $botConfig));

            expect($selections)->toHaveCount(1);
            expect(array_keys($selections))->toBe(['message']);
        });

        it('skips null properties', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $processor = Mockery::mock(\BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract::class);

            $registry->register(MessageTypeDTO::class, $processor);

            $selector = createTestSelectorWithRegistry($registry);

            $updateDTO = new UpdateTypeDTO(updateId: 1);

            $botConfig = new TgBotConfig(token: '123456789:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

            $selections = iterator_to_array($selector->selectProcessors($updateDTO, $botConfig));

            expect($selections)->toBe([]);
        });

        it('only includes processors that support the DTO', function () {
            $registry = TypeDTOProcessorRegistry::build();
            $supporting = new class () implements \BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract {
                public function support(...$a): bool
                {
                    return true;
                }
                public function process(...$a): void
                {
                }
                public static function build(...$a): static
                {
                    throw new \RuntimeException('not impl');
                }
                public function isStrictOrdered(...$a): bool
                {
                    return false;
                }
                public function isNeedUpdateDTO(): bool
                {
                    return false;
                }
                public function executionKey(...$a): ?string
                {
                    return null;
                }
            };
            $notSupporting = new class () implements \BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract {
                public function support(...$a): bool
                {
                    return false;
                }
                public function process(...$a): void
                {
                }
                public static function build(...$a): static
                {
                    throw new \RuntimeException('not impl');
                }
                public function isStrictOrdered(...$a): bool
                {
                    return false;
                }
                public function isNeedUpdateDTO(): bool
                {
                    return false;
                }
                public function executionKey(...$a): ?string
                {
                    return null;
                }
            };

            $registry->register(MessageTypeDTO::class, $supporting);
            $registry->register(MessageTypeDTO::class, $notSupporting);

            $selector = createTestSelectorWithRegistry($registry);

            $chat = new ChatTypeDTO(id: '1', type: ChatPropTypeEnum::PRIVATE);
            $message = new MessageTypeDTO(messageId: 1, date: 1000000, chat: $chat);
            $updateDTO = new UpdateTypeDTO(updateId: 1, message: $message);

            $botConfig = new TgBotConfig(token: '123456789:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

            $selections = iterator_to_array($selector->selectProcessors($updateDTO, $botConfig));

            expect($selections)->toHaveCount(1);
            expect($selections['message'])->toHaveCount(1);
        });
    });
});
