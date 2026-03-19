<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Outbound\Ordering\DefaultOrderingStrategy;
use BAGArt\TelegramBot\Outbound\TgSender;

if (!class_exists('ControllableClock')) {
    require_once __DIR__.'/../../Helpers.php';
}

describe('TgSender', function () {
    it('push task into queue when send is called', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(
            new ControllableClock(),
        );

        $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);

        $mapper = Mockery::mock(TgApiDTOMapperContract::class);
        $mapper->shouldReceive('toArray')
            ->with($dto)
            ->once()
            ->andReturn(['chat_id' => 123, 'text' => 'hello']);

        $sender = new TgSender($queue, $mapper, new DefaultOrderingStrategy());

        $botConfig = new TgBotConfig(token: '12345:abcde12345abcde12345abcde12345abcde');

        $sender->send($botConfig, $dto);

        expect($queue->size())->toBe(1);

        $envelope = $queue->pop();
        expect($envelope)->not->toBeNull()
            ->and($envelope->task->botConfig->botId)->toBe('12345')
            ->and($envelope->task->dtoClass)->toBe($dto::class)
            ->and($envelope->task->dtoData)->toBe(['chat_id' => 123, 'text' => 'hello'])
            ->and($envelope->task->orderingKey)->toBe('123')
            ->and($envelope->task->priority)->toBe(\BAGArt\TelegramBot\Outbound\TaskPriority::Normal);
    });

    it('sets orderingKey to null for broadcast (no chat_id)', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(
            new ControllableClock(),
        );

        $dto = Mockery::mock(\BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract::class);

        $mapper = Mockery::mock(TgApiDTOMapperContract::class);
        $mapper->shouldReceive('toArray')
            ->with($dto)
            ->once()
            ->andReturn(['text' => 'broadcast']);

        $sender = new TgSender($queue, $mapper, new DefaultOrderingStrategy());

        $botConfig = new TgBotConfig(token: '12345:abcde12345abcde12345abcde12345abcde');

        $sender->send($botConfig, $dto);

        $envelope = $queue->pop();
        expect($envelope->task->orderingKey)->toBeNull();
    });
});
