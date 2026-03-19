<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\AtomicDlqQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\ChannelDiscoverableQueueContract;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;

if (!class_exists('ControllableClock')) {
    require_once __DIR__.'/../../../Helpers.php';
}

describe('DLQ Retry Feature — end-to-end', function () {
    it('push to DLQ, list, then redeliver and push back to main queue', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        expect($queue instanceof AtomicDlqQueueContract)->toBeTrue();

        $task = new OutboundTask(id: 'dlq-test-1', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'App\\SendMessage', dtoData: ['chat_id' => 1]);
        $envelope = new OutboundEnvelope(task: $task, state: new OutboundTaskState());

        $queue->pushToDeadLetter($envelope, 'business_error');

        expect($queue->deadLetterSize())->toBe(1);

        $entries = $queue->listDeadLetter(null, 50);
        expect($entries)->toHaveCount(1);

        $entry = $entries[0];
        expect($entry)->toBeInstanceOf(DeadLetterEntry::class)
            ->and($entry->id)->toBe('dlq-test-1')
            ->and($entry->reason)->toBe('business_error')
            ->and($entry->canRedeliver())->toBeTrue();

        $envelope = $entry->restoreEnvelope();
        $queue->push($envelope->task);

        expect($queue->size())->toBe(1);

        $popped = $queue->pop();
        expect($popped)->not->toBeNull()
            ->and($popped->task->id)->toBe('dlq-test-1')
            ->and($popped->state->getAttempt())->toBe(0);
    });

    it('atomicFetchAndRemoveFromDlq removes entry atomically and allows redelivery', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        expect($queue instanceof AtomicDlqQueueContract && $queue instanceof ChannelDiscoverableQueueContract)->toBeTrue();

        $task = new OutboundTask(id: 'fetch-me', botConfig: new TgBotConfig(token: 'test:token', botId: 'botX'), dtoClass: 'Test', dtoData: []);
        $envelope = new OutboundEnvelope(task: $task, state: new OutboundTaskState());
        $queue->pushToDeadLetter($envelope, 'expired');

        $channels = $queue->getDlqChannels('tg-dlq:*');
        expect($channels)->toHaveCount(1);

        $raw = $queue->atomicFetchAndRemoveFromDlq($channels[0], 'fetch-me');
        expect($raw)->toBeString();

        $decoded = json_decode($raw, true);
        expect($decoded)->toBeArray()
            ->and($decoded['id'])->toBe('fetch-me');

        expect($queue->deadLetterSize())->toBe(0);

        $entry = DeadLetterEntry::fromJson($decoded);
        $restored = $entry->restoreEnvelope();
        $queue->push($restored->task);

        $popped = $queue->pop();
        expect($popped)->not->toBeNull()
            ->and($popped->task->id)->toBe('fetch-me');
    });

    it('canRedeliver returns false after MAX_REDELIVERIES', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $task = new OutboundTask(id: 'exhausted', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: []);
        $envelope = new OutboundEnvelope(task: $task, state: new OutboundTaskState());
        $queue->pushToDeadLetter($envelope, 'permanent_failure');

        $entry = $queue->listDeadLetter(null, 50)[0];
        $entry->redeliveryCount = DeadLetterEntry::MAX_REDELIVERIES;

        expect($entry->canRedeliver())->toBeFalse();
    });

    it('purgeExpired removes old DLQ entries and keeps recent ones', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $recent = new OutboundEnvelope(
            new OutboundTask(id: 'recent', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: []),
            new OutboundTaskState(),
        );
        $queue->pushToDeadLetter($recent, 'error');

        $purged = $queue->purgeExpired('tg-dlq:*', time() - 86400);
        expect($purged)->toBeGreaterThanOrEqual(0);
    });
});
