<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TaskPriority;

describe('DeadLetterEntry', function () {
    it('builds a snapshot from an envelope via fromEnvelope', function () {
        $task = new OutboundTask(
            id: 'task-id-1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot-9'),
            dtoClass: 'App\\SendMessage',
            dtoData: ['chat_id' => 1, 'text' => 'hi'],
            priority: TaskPriority::High,
        );
        $state = new OutboundTaskState(status: OutboundTaskState::STATUS_IN_PROGRESS, attempt: 5);
        $envelope = new OutboundEnvelope($task, $state);

        $entry = DeadLetterEntry::fromEnvelope($envelope, 'max_attempts');

        expect($entry->id)->toBe('task-id-1')
            ->and($entry->reason)->toBe('max_attempts')
            ->and($entry->redeliveryCount)->toBe(0)
            ->and($entry->originalTask['id'])->toBe('task-id-1')
            ->and($entry->originalState['attempt'])->toBe(5);
    });

    it('records failedAt as a parseable ISO 8601 timestamp', function () {
        $envelope = new OutboundEnvelope(
            new OutboundTask(id: 't', botConfig: new TgBotConfig(token: 'test:token', botId: 'b'), dtoClass: 'D', dtoData: []),
            new OutboundTaskState(),
        );
        $entry = DeadLetterEntry::fromEnvelope($envelope, 'expired');

        $parsed = new DateTimeImmutable($entry->failedAt);

        expect($parsed)->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('restoreEnvelope resets attempt to 0 and status to pending', function () {
        $task = new OutboundTask(id: 't', botConfig: new TgBotConfig(token: 'test:token', botId: 'b'), dtoClass: 'D', dtoData: []);
        $state = new OutboundTaskState(status: OutboundTaskState::STATUS_BUSINESS_ERROR, attempt: 5);
        $envelope = new OutboundEnvelope($task, $state);

        $entry = DeadLetterEntry::fromEnvelope($envelope, 'bad_request');
        $restored = $entry->restoreEnvelope();

        expect($restored->task->id)->toBe('t')
            ->and($restored->state->getAttempt())->toBe(0)
            ->and($restored->state->getStatus())->toBe(OutboundTaskState::STATUS_PENDING)
            ->and($restored->state->getLastError())->toBe('bad_request')
            ->and($restored->state->getErrorContext())->toHaveKey('redelivered_from_dlq');
    });

    it('canRedeliver is true until MAX_REDELIVERIES reached', function () {
        $entry = new DeadLetterEntry(
            id: 'e',
            reason: 'r',
            failedAt: '2026-07-08T12:00:00+00:00',
            originalTask: ['id' => 't', 'botConfig' => ['token' => 'test:token', 'botId' => 'b'], 'dtoClass' => 'D', 'dtoData' => [], 'priority' => 1, 'orderingKey' => null, 'createdAt' => '2026-07-08T12:00:00+00:00', 'schemaVersion' => 1],
            originalState: ['status' => 'business_error', 'attempt' => 3, 'lastError' => null, 'errorContext' => null],
            redeliveryCount: 0,
        );

        expect($entry->canRedeliver())->toBeTrue();

        $entry->redeliveryCount = DeadLetterEntry::MAX_REDELIVERIES;

        expect($entry->canRedeliver())->toBeFalse();
    });

    it('round-trips through jsonSerialize / fromJson preserving redeliveryCount', function () {
        $entry = new DeadLetterEntry(
            id: 'e-2',
            reason: 'expired',
            failedAt: '2026-07-08T12:00:00+00:00',
            originalTask: ['id' => 't', 'botConfig' => ['token' => 'test:token', 'botId' => 'b'], 'dtoClass' => 'D', 'dtoData' => [], 'priority' => 1, 'orderingKey' => null, 'createdAt' => '2026-07-08T12:00:00+00:00', 'schemaVersion' => 1],
            originalState: ['status' => 'business_error', 'attempt' => 3, 'lastError' => 'expired', 'errorContext' => null],
            redeliveryCount: 2,
        );

        $restored = DeadLetterEntry::fromJson($entry->jsonSerialize());

        expect($restored->id)->toBe('e-2')
            ->and($restored->reason)->toBe('expired')
            ->and($restored->redeliveryCount)->toBe(2)
            ->and($restored->originalTask['id'])->toBe('t')
            ->and($restored->originalState['attempt'])->toBe(3);
    });

    it('fromJson defaults redeliveryCount to 0 when absent (back-compat)', function () {
        $restored = DeadLetterEntry::fromJson([
            'id' => 'e-3',
            'reason' => 'r',
            'failedAt' => '2026-07-08T12:00:00+00:00',
            'originalTask' => ['id' => 't', 'botId' => 'b', 'dtoClass' => 'D', 'dtoData' => [], 'priority' => 1, 'orderingKey' => null, 'createdAt' => '2026-07-08T12:00:00+00:00', 'schemaVersion' => 1],
            'originalState' => ['status' => 'business_error', 'attempt' => 1, 'lastError' => null, 'errorContext' => null],
        ]);

        expect($restored->redeliveryCount)->toBe(0);
    });

    it('rejects unknown schemaVersion', function () {
        expect(fn () => DeadLetterEntry::fromJson([
            'id' => 'e',
            'reason' => 'r',
            'failedAt' => '2026-07-08T12:00:00+00:00',
            'originalTask' => [],
            'originalState' => [],
            'schemaVersion' => 5,
        ]))->toThrow(RuntimeException::class);
    });
});
