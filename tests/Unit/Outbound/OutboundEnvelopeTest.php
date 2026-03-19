<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TaskPriority;

describe('OutboundEnvelope', function () {
    it('carries task and state, with deliveryId null by default', function () {
        $task = new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'b'), dtoClass: 'D', dtoData: []);
        $state = new OutboundTaskState();
        $envelope = new OutboundEnvelope($task, $state);

        expect($envelope->task)->toBe($task)
            ->and($envelope->state)->toBe($state)
            ->and($envelope->deliveryId)->toBeNull();
    });

    it('allows setting deliveryId (filled at pop time by the adapter)', function () {
        $task = new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'b'), dtoClass: 'D', dtoData: []);
        $envelope = new OutboundEnvelope($task, new OutboundTaskState());
        $envelope->deliveryId = 'delivery-xyz';

        expect($envelope->deliveryId)->toBe('delivery-xyz');
    });

    it('state is mutable through the envelope reference', function () {
        $task = new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'b'), dtoClass: 'D', dtoData: []);
        $state = new OutboundTaskState();
        $envelope = new OutboundEnvelope($task, $state);

        $envelope->state->incrementAttempt();

        expect($state->getAttempt())->toBe(1)
            ->and($envelope->state->getAttempt())->toBe(1);
    });

    it('does NOT serialize deliveryId (it is regenerated per pop)', function () {
        $task = new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'b'), dtoClass: 'D', dtoData: []);
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(), 'should-not-serialize');

        $json = $envelope->jsonSerialize();

        expect($json)->not->toHaveKey('deliveryId');
    });

    it('round-trips through jsonSerialize / fromJson without deliveryId', function () {
        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot-3'),
            dtoClass: 'App\\SendMessage',
            dtoData: ['chat_id' => 5, 'text' => 'hi'],
            priority: TaskPriority::High,
            orderingKey: '5:game-1',
        );
        $state = new OutboundTaskState(status: OutboundTaskState::STATUS_IN_PROGRESS, attempt: 2);
        $envelope = new OutboundEnvelope($task, $state, 'delivery-7');

        $restored = OutboundEnvelope::fromJson($envelope->jsonSerialize());

        expect($restored->task->id)->toBe('t1')
            ->and($restored->task->botConfig->botId)->toBe('bot-3')
            ->and($restored->task->dtoData)->toBe(['chat_id' => 5, 'text' => 'hi'])
            ->and($restored->task->priority)->toBe(TaskPriority::High)
            ->and($restored->task->orderingKey)->toBe('5:game-1')
            ->and($restored->state->getAttempt())->toBe(2)
            ->and($restored->state->getStatus())->toBe(OutboundTaskState::STATUS_IN_PROGRESS)
            ->and($restored->deliveryId)->toBeNull();
    });

    it('rejects unknown schemaVersion', function () {
        expect(fn () => OutboundEnvelope::fromJson(['schemaVersion' => 42, 'task' => [], 'state' => []]))
            ->toThrow(RuntimeException::class);
    });
});
