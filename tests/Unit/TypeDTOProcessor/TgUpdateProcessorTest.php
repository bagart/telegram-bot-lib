<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgUpdateProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\TgUpdateProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;

describe('TgUpdateProcessor', function () {
    describe('support()', function () {
        it('supports UpdateTypeDTO', function () {
            $registry = new TypeDTOProcessorRegistry();
            $processor = new TgUpdateProcessor($registry);

            $dto = Mockery::mock(UpdateTypeDTO::class);

            expect($processor->support($dto))->toBeTrue();
        });

        it('does not support non-UpdateTypeDTO', function () {
            $registry = new TypeDTOProcessorRegistry();
            $processor = new TgUpdateProcessor($registry);

            $dto = Mockery::mock(MessageTypeDTO::class);

            expect($processor->support($dto))->toBeFalse();
        });
    });

    describe('process()', function () {
        it('processes update and dispatches to registry', function () {
            $registry = new TypeDTOProcessorRegistry();
            $processor = new TgUpdateProcessor($registry);

            $updateDTO = Mockery::mock(UpdateTypeDTO::class);
            $updateDTO->shouldReceive('tgPropertyMetas')->andReturn([]);
            $updateDTO->shouldReceive('getMessage')->andReturn(null);
            $updateDTO->shouldReceive('getEditedMessage')->andReturn(null);
            $updateDTO->shouldReceive('getChannelPost')->andReturn(null);
            $updateDTO->shouldReceive('getEditedChannelPost')->andReturn(null);
            $updateDTO->shouldReceive('getInlineQuery')->andReturn(null);
            $updateDTO->shouldReceive('getChosenInlineResult')->andReturn(null);
            $updateDTO->shouldReceive('getCallbackQuery')->andReturn(null);
            $updateDTO->shouldReceive('getShippingQuery')->andReturn(null);
            $updateDTO->shouldReceive('getPreCheckoutQuery')->andReturn(null);
            $updateDTO->shouldReceive('getPoll')->andReturn(null);
            $updateDTO->shouldReceive('getPollAnswer')->andReturn(null);
            $updateDTO->shouldReceive('getMyChatMember')->andReturn(null);
            $updateDTO->shouldReceive('getChatMember')->andReturn(null);
            $updateDTO->shouldReceive('getChatJoinRequest')->andReturn(null);
            $updateDTO->shouldReceive('getChatBoost')->andReturn(null);
            $updateDTO->shouldReceive('getRemovedChatBoost')->andReturn(null);

            // Should not throw
            $processor->process($updateDTO, '123456789');

            expect(true)->toBeTrue();
        });
    });
});
