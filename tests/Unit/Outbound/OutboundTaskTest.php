<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\TaskPriority;

describe('OutboundTask', function () {
    it('constructs with defaults (Normal priority, no orderingKey, now createdAt)', function () {
        $task = new OutboundTask(
            id: 'abc123',
            botConfig: new TgBotConfig(token: 'test:token', botId: '42'),
            dtoClass: 'Some\\Dto',
            dtoData: ['chat_id' => 1, 'text' => 'hi'],
        );

        expect($task->priority)->toBe(TaskPriority::Normal)
            ->and($task->orderingKey)->toBeNull()
            ->and($task->schemaVersion)->toBe(1)
            ->and($task->id)->toBe('abc123');
    });

    it('round-trips through jsonSerialize / fromJson preserving all fields', function () {
        $createdAt = new DateTimeImmutable('2026-07-08T12:00:00+00:00');
        $task = new OutboundTask(
            id: 'id-1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot-7'),
            dtoClass: 'App\\SendMsg',
            dtoData: ['chat_id' => 99, 'text' => 'hello'],
            priority: TaskPriority::High,
            orderingKey: '99:session-1',
            createdAt: $createdAt,
        );

        $json = $task->jsonSerialize();
        $restored = OutboundTask::fromJson($json);

        expect($restored->id)->toBe('id-1')
            ->and($restored->botConfig->botId)->toBe('bot-7')
            ->and($restored->dtoClass)->toBe('App\\SendMsg')
            ->and($restored->dtoData)->toBe(['chat_id' => 99, 'text' => 'hello'])
            ->and($restored->priority)->toBe(TaskPriority::High)
            ->and($restored->orderingKey)->toBe('99:session-1')
            ->and($restored->createdAt->format(DateTimeInterface::ATOM))->toBe($createdAt->format(DateTimeInterface::ATOM))
            ->and($restored->schemaVersion)->toBe(1);
    });

    it('serializes priority as its int value', function () {
        $task = new OutboundTask(
            id: 'x',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: [],
            priority: TaskPriority::Critical,
        );

        expect($task->jsonSerialize()['priority'])->toBe(3);
    });

    it('treats dtoData with chat_id as string when restoring ordering fallback', function () {
        $restored = OutboundTask::fromJson([
            'id' => 'x',
            'botConfig' => ['token' => 'test:token', 'botId' => 'b'],
            'dtoClass' => 'D',
            'dtoData' => ['chat_id' => '12345'],
            'priority' => 0,
            'orderingKey' => null,
            'createdAt' => '2026-07-08T12:00:00+00:00',
            'schemaVersion' => 1,
        ]);

        expect($restored->dtoData['chat_id'])->toBe('12345')
            ->and($restored->priority)->toBe(TaskPriority::Low);
    });

    it('rejects unknown schemaVersion', function () {
        expect(fn () => OutboundTask::fromJson([
            'id' => 'x',
            'botConfig' => ['token' => 'test:token', 'botId' => 'b'],
            'dtoClass' => 'D',
            'dtoData' => [],
            'priority' => 1,
            'orderingKey' => null,
            'createdAt' => '2026-07-08T12:00:00+00:00',
            'schemaVersion' => 99,
        ]))->toThrow(RuntimeException::class);
    });

    it('computes age from createdAt relative to now', function () {
        $createdAt = new DateTimeImmutable('-100 seconds');
        $task = new OutboundTask(
            id: 'x',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: [],
            createdAt: $createdAt,
        );

        // age() is max(0, now - createdAt). With now = time(), should be ~100.
        expect($task->age(time()))->toBeGreaterThanOrEqual(99)
            ->and($task->age(time()))->toBeLessThanOrEqual(105);
    });

    it('never returns negative age', function () {
        $future = new DateTimeImmutable('+100 seconds');
        $task = new OutboundTask(
            id: 'x',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'D',
            dtoData: [],
            createdAt: $future,
        );

        expect($task->age(time()))->toBe(0);
    });
});

describe('TaskPriority', function () {
    it('maps each case to its integer value ascending', function () {
        expect(TaskPriority::Low->value)->toBe(0)
            ->and(TaskPriority::Normal->value)->toBe(1)
            ->and(TaskPriority::High->value)->toBe(2)
            ->and(TaskPriority::Critical->value)->toBe(3);
    });

    it('restores from integer via from()', function () {
        expect(TaskPriority::from(2))->toBe(TaskPriority::High);
    });
});
