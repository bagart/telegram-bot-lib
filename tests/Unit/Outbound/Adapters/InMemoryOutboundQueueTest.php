<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\OutboundBackpressureException;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * Hand-rollable fake clock — allows shifting time in lease expiry tests.
 */
class ControllableClock implements ASKClockContract
{
    public int $time;

    public function __construct(int $startTime = 1000000)
    {
        $this->time = $startTime;
    }

    public function advance(int $seconds): void
    {
        $this->time += $seconds;
    }

    public function microtime(): float
    {
        return (float)$this->time;
    }

    public function time(): int
    {
        return $this->time;
    }

    public function timeMs(): int
    {
        return $this->time * 1000;
    }

    public function hrtime(): int
    {
        return $this->time * ASKClockContract::NS_PER_SEC;
    }

    public function sleep(int $microseconds): void
    {
        $this->advance((int)($microseconds / 1_000_000));
    }

    public function getSecondsFromInterval(DateInterval $interval): int
    {
        return 0;
    }
}

function makeTask(
    string $id = 't1',
    TaskPriority $priority = TaskPriority::Normal,
    ?string $orderingKey = null,
    string $botId = 'bot1',
): OutboundTask {
    return new OutboundTask(
        id: $id,
        botConfig: new TgBotConfig(token: 'test:token', botId: $botId),
        dtoClass: 'App\\SendMessage',
        dtoData: ['chat_id' => 1, 'text' => 'hi'],
        priority: $priority,
        orderingKey: $orderingKey,
    );
}

describe('InMemoryOutboundQueueContractContractContractContract — push/pop/ack', function () {
    it('push then pop returns the task with a deliveryId', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1'));
        $envelope = $queue->pop();

        expect($envelope)->not->toBeNull()
            ->and($envelope->task->id)->toBe('t1')
            ->and($envelope->deliveryId)->not->toBeNull()
            ->and($envelope->state->getStatus())->toBe(OutboundTaskState::STATUS_PENDING);
    });

    it('pop returns null when the queue is empty', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        expect($queue->pop())->toBeNull();
    });

    it('size counts ready + delayed (not inflight)', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1'));
        $queue->push(makeTask('t2'));

        expect($queue->size())->toBe(2);

        $queue->pop();

        expect($queue->size())->toBe(1);
    });

    it('ack removes the in-flight task', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1'));
        $envelope = $queue->pop();

        $queue->ack($envelope);

        expect($queue->size())->toBe(0)
            ->and($queue->pop())->toBeNull();
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — priority ordering', function () {
    it('pops higher priority before lower priority', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('low', TaskPriority::Low));
        $queue->push(makeTask('high', TaskPriority::High));
        $queue->push(makeTask('normal', TaskPriority::Normal));

        expect($queue->pop()->task->id)->toBe('high')
            ->and($queue->pop()->task->id)->toBe('normal')
            ->and($queue->pop()->task->id)->toBe('low');
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — release / retry', function () {
    it('release with delay puts the task in delayed (not immediately poppable)', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(makeTask('t1'));
        $envelope = $queue->pop();

        $queue->release($envelope, delaySec: 10);

        // Not immediately available.
        expect($queue->pop())->toBeNull();

        // After delay expires — available.
        $clock->advance(11);
        $retried = $queue->pop();

        expect($retried)->not->toBeNull()
            ->and($retried->task->id)->toBe('t1');
    });

    it('release with delay=0 makes the task immediately available', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1'));
        $envelope = $queue->pop();

        $queue->release($envelope, delaySec: 0);

        expect($queue->pop())->not->toBeNull();
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — visibility lease', function () {
    it('a popped task is not re-popped while lease is valid', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(makeTask('t1'));
        $queue->pop(60);

        expect($queue->pop())->toBeNull();
    });

    it('an expired lease is reclaimed (lazy crash recovery)', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(makeTask('t1'));
        $queue->pop(60);

        // Lease has not expired yet.
        $clock->advance(50);
        expect($queue->pop())->toBeNull();

        // After lease expires — the task is available again.
        $clock->advance(11);
        $reclaimed = $queue->pop();

        expect($reclaimed)->not->toBeNull()
            ->and($reclaimed->task->id)->toBe('t1');
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — renewLease', function () {
    it('renewLease extends the lease so the task stays hidden beyond the original lease', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(makeTask('t1'));
        $envelope = $queue->pop(60);
        // t=0: original lease = 0 + 60 = 60.

        // Renew midway through processing (renew = now + 60).
        $clock->advance(50); // t=50
        expect($queue->renewLease($envelope, 60))->toBeTrue();
        // Now lease = 50 + 60 = 110.

        // Without renewal at t=105 the task would have been returned (105 > 60).
        // With renewal (110 > 105) — the task is still hidden.
        $clock->advance(55); // t=105
        expect($queue->pop())->toBeNull();
    });

    it('a renewed task eventually expires after the renewed lease', function () {
        $clock = new ControllableClock();
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(makeTask('t1'));
        $envelope = $queue->pop(60);

        $clock->advance(50); // t=50
        $queue->renewLease($envelope, 60); // lease = 110

        // After the renewed lease expires — the task is available again.
        $clock->advance(65); // t=115 > 110
        expect($queue->pop())->not->toBeNull();
    });

    it('renewLease returns false for an unknown deliveryId', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $envelope = new OutboundEnvelope(makeTask(), new OutboundTaskState(), 'unknown-id');

        expect($queue->renewLease($envelope, 60))->toBeFalse();
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — ordering (OutboundOrderingQueueContract)', function () {
    it('lockNextReadyKey returns null when no keys are ready', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        expect($queue->lockNextReadyKey())->toBeNull();
    });

    it('lockNextReadyKey returns a key after push with orderingKey', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1', orderingKey: 'chat:1'));

        $key = $queue->lockNextReadyKey();
        expect($key)->toBe('chat:1');
    });

    it('lockNextReadyKey returns null after key is consumed by pop', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1', orderingKey: 'chat:1'));
        $queue->pop();

        expect($queue->lockNextReadyKey())->toBeNull();
    });

    it('refreshKeyState returns key to ready_keys when queue has more tasks', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1', orderingKey: 'chat:1'));
        $queue->push(makeTask('t2', orderingKey: 'chat:1'));

        $first = $queue->pop();
        expect($first->task->id)->toBe('t1');

        // After ack, key should be refreshed
        $queue->ack($first);

        $key = $queue->lockNextReadyKey();
        expect($key)->toBe('chat:1');
    });

    it('refreshKeyState does not return key when queue is empty', function () {
        $queue = new \BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue(new ControllableClock());

        $queue->push(makeTask('t1', orderingKey: 'chat:1'));
        $first = $queue->pop();
        $queue->ack($first);

        // No more tasks for this key
        expect($queue->lockNextReadyKey())->toBeNull();
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — backpressure', function () {
    it('push throws when maxSize is reached', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock(), maxSize: 2);

        $queue->push(makeTask('t1'));
        $queue->push(makeTask('t2'));

        expect(fn () => $queue->push(makeTask('t3')))
            ->toThrow(OutboundBackpressureException::class);
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — Dead Letter Queue', function () {
    it('pushToDeadLetter stores and returns an entryId', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $envelope = new OutboundEnvelope(makeTask('t1'), new OutboundTaskState());
        $entryId = $queue->pushToDeadLetter($envelope, 'bad_request');

        expect($entryId)->toBe('t1')
            ->and($queue->deadLetterSize())->toBe(1);
    });

    it('listDeadLetter returns DeadLetterEntry objects', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $envelope = new OutboundEnvelope(makeTask('t1'), new OutboundTaskState());
        $queue->pushToDeadLetter($envelope, 'expired');

        $entries = $queue->listDeadLetter(null);

        expect($entries)->toHaveCount(1)
            ->and($entries[0])->toBeInstanceOf(DeadLetterEntry::class)
            ->and($entries[0]->reason)->toBe('expired');
    });

    it('atomicFetchAndRemoveFromDlq extracts and deletes the entry', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $envelope = new OutboundEnvelope(makeTask('t1'), new OutboundTaskState());
        $queue->pushToDeadLetter($envelope, 'bad_request');

        $json = $queue->atomicFetchAndRemoveFromDlq('tg-dlq:bot1', 't1');

        expect($json)->not->toBeNull()
            ->and($queue->deadLetterSize())->toBe(0);
    });

    it('atomicFetchAndRemoveFromDlq returns null for a missing entry', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        expect($queue->atomicFetchAndRemoveFromDlq('tg-dlq:bot1', 'nope'))->toBeNull();
    });

    it('getDlqChannels matches channels by glob pattern', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->pushToDeadLetter(new OutboundEnvelope(makeTask('t1', botId: 'bot1'), new OutboundTaskState()), 'r');
        $queue->pushToDeadLetter(new OutboundEnvelope(makeTask('t2', botId: 'bot2'), new OutboundTaskState()), 'r');

        expect($queue->getDlqChannels('tg-dlq:*'))->toBe(['tg-dlq:bot1', 'tg-dlq:bot2'])
            ->and($queue->getDlqChannels('tg-dlq:bot1'))->toBe(['tg-dlq:bot1']);
    });
});

describe('InMemoryOutboundQueueContractContractContractContract — purgeExpired', function () {
    it('removes DLQ entries older than the threshold', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        // DeadLetterEntry::fromEnvelope uses wall-clock time for failedAt.
        // Create an 'old' entry with an explicit past-timestamp and a 'recent' one with now.
        $oldEntry = new DeadLetterEntry(
            id: 'old',
            reason: 'expired',
            failedAt: (new DateTimeImmutable('-2 hours'))->format(DateTimeInterface::ATOM),
            originalTask: (new OutboundTask(id: 'old', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: []))->jsonSerialize(),
            originalState: (new OutboundTaskState())->jsonSerialize(),
        );
        $recentEnvelope = new OutboundEnvelope(makeTask('recent'), new OutboundTaskState());

        // pushToDeadLetter creates a fresh entry via fromEnvelope (now).
        // 'Old' one is injected via a listDeadLetter-independent path: directly
        // using the interface — in-memory has no public API for raw writes,
        // so we test purge through a single entry with past failedAt vs threshold.
        $queue->pushToDeadLetter($recentEnvelope, 'bad_request');

        // threshold = now → 'recent' (failedAt ≈ now) stays if threshold > failedAt.
        // Set threshold in the future → both entries are 'older' → purge removes recent.
        $futureThreshold = time() + 3600;
        $purged = $queue->purgeExpired('tg-dlq:*', $futureThreshold);

        expect($purged)->toBe(1)
            ->and($queue->deadLetterSize())->toBe(0);
    });

    it('keeps entries newer than the threshold', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $queue->pushToDeadLetter(new OutboundEnvelope(makeTask('t1'), new OutboundTaskState()), 'r');

        // threshold far in the past → nothing is removed.
        $purged = $queue->purgeExpired('tg-dlq:*', time() - 86400);

        expect($purged)->toBe(0)
            ->and($queue->deadLetterSize())->toBe(1);
    });
});
