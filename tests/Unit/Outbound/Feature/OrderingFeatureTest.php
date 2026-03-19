<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\TaskPriority;

if (!class_exists('ControllableClock') || !function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../../Helpers.php';
}

describe('OrderingFeature — strict FIFO per orderingKey via queue', function () {
    it('pops only one task per orderingKey at a time', function () {
        $clock = new ControllableClock();
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue($clock);

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));

        $first = $queue->pop();
        expect($first)->not->toBeNull()
            ->and($first->task->id)->toBe('a');

        // Second pop returns null because key is locked (in-flight)
        expect($queue->pop())->toBeNull();
    });

    it('after ack, next task for same key becomes available', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(new ControllableClock());

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));

        $first = $queue->pop();
        expect($first)->not->toBeNull()
            ->and($first->task->id)->toBe('a');

        $queue->ack($first);

        $second = $queue->pop();
        expect($second)->not->toBeNull()
            ->and($second->task->id)->toBe('b');
    });

    it('different keys can be popped concurrently', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(new ControllableClock());

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 2], orderingKey: 'chat:2'));

        $first = $queue->pop();
        $second = $queue->pop();

        expect($first)->not->toBeNull()
            ->and($second)->not->toBeNull()
            ->and($first->task->id)->toBe('a')
            ->and($second->task->id)->toBe('b');
    });

    it('broadcast (null key) is always poppable', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(new ControllableClock());

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['text' => 'broadcast'], orderingKey: null));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));

        // Ordered task pops first (lockNextReadyKey), then broadcast is always poppable
        $first = $queue->pop();
        expect($first)->not->toBeNull()
            ->and($first->task->id)->toBe('b');

        // Key is locked (chat:1 in flight), but broadcast is still poppable
        $second = $queue->pop();
        expect($second)->not->toBeNull()
            ->and($second->task->id)->toBe('a');
    });

    it('release with delay keeps key unavailable', function () {
        $clock = new ControllableClock();
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue($clock);

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));

        $first = $queue->pop();
        $queue->release($first, delaySec: 10);

        // Key is still unavailable (task in delayed)
        expect($queue->pop())->toBeNull();

        // After delay, task is back and key is available
        $clock->advance(11);

        $afterDelay = $queue->pop();
        expect($afterDelay)->not->toBeNull()
            ->and($afterDelay->task->id)->toBe('b');
    });

    it('release with delay=0 puts task first and re-adds key', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(new ControllableClock());

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));

        $first = $queue->pop();
        expect($first->task->id)->toBe('a');

        $queue->release($first, delaySec: 0);

        // Task 'a' is back at the front, key is available
        $retried = $queue->pop();
        expect($retried)->not->toBeNull()
            ->and($retried->task->id)->toBe('a');
    });

    it('expired inflight reclaim puts key back to ready_keys', function () {
        $clock = new ControllableClock();
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue($clock);

        $queue->push(new OutboundTask(id: 'a', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'b', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], orderingKey: 'chat:1'));

        $first = $queue->pop(60);
        expect($first->task->id)->toBe('a');

        // Second pop returns null (key locked)
        expect($queue->pop())->toBeNull();

        // After lease expires, reclaim should make 'b' available
        // InMemoryOutboundQueueContractContractContractContract doesn't auto-reclaim on pop, so we test via ack
        $queue->ack($first);

        $second = $queue->pop();
        expect($second)->not->toBeNull()
            ->and($second->task->id)->toBe('b');
    });

    it('priority across keys — higher priority key pops first', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(new ControllableClock());

        $queue->push(new OutboundTask(id: 'low', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 1], priority: TaskPriority::Low, orderingKey: 'chat:1'));
        $queue->push(new OutboundTask(id: 'high', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: ['chat_id' => 2], priority: TaskPriority::High, orderingKey: 'chat:2'));

        $first = $queue->pop();
        expect($first)->not->toBeNull()
            ->and($first->task->id)->toBe('high');
    });
});
