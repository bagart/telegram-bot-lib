<?php

declare(strict_types=1);

use BAGArt\ASKClientRedis\Redis\Client\PhpRedisAdapter;
use BAGArt\ASKClientRedis\Redis\RedisDsn;
use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\Adapters\RedisOutboundQueue;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * RedisOutboundQueue integration test against a live Redis.
 *
 * Context: Redis-dependent tests are excluded from the default suite (phpunit.xml line 19).
 * Here — skip-guard: if Redis is unavailable, tests are skipped (covers nothing).
 * Manual run: php vendor/bin/pest tests/Unit/Outbound/Adapters/RedisOutboundQueueTest.php
 */

/**
 * Test utility: returns a connected Redis instance or null.
 */
function connectTestRedis(): ?Redis
{
    if (! extension_loaded('redis')) {
        return null;
    }
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379, 2.0);
        if (! $redis->ping()) {
            return null;
        }

        return $redis;
    } catch (Throwable) {
        return null;
    }
}

/** Hand-rolled fake clock for lease expiry tests. */
class RedisTestClock implements ASKClockContract
{
    public int $time;

    public function __construct(int $start = 1000000)
    {
        $this->time = $start;
    }

    public function advance(int $seconds): void
    {
        $this->time += $seconds;
    }

    public function microtime(): float
    {
        return (float) $this->time;
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
        $this->advance((int) ($microseconds / 1_000_000));
    }

    public function getSecondsFromInterval(DateInterval $interval): int
    {
        return 0;
    }
}

function makeRedisTask(
    string $id = 't1',
    TaskPriority $priority = TaskPriority::Normal,
    ?string $orderingKey = null,
    string $botId = 'bot1',
    ?DateTimeImmutable $createdAt = null,
): OutboundTask {
    return new OutboundTask(
        id: $id,
        botConfig: new TgBotConfig(token: 'test:token', botId: $botId),
        dtoClass: 'App\\SendMessage',
        dtoData: ['chat_id' => 1, 'text' => 'hi'],
        priority: $priority,
        orderingKey: $orderingKey,
        createdAt: $createdAt ?? new DateTimeImmutable(),
    );
}

/** createdAt aligned to the fake test clock so aging math is deterministic. */
function makeRedisTaskOnClock(
    RedisTestClock $clock,
    string $id,
    TaskPriority $priority = TaskPriority::Normal,
): OutboundTask {
    return makeRedisTask($id, $priority, createdAt: (new DateTimeImmutable())->setTimestamp($clock->time));
}

// Before each test: clean queue keys.
uses()->beforeEach(function () {
    $redis = connectTestRedis();
    if ($redis === null) {
        test()->skip(
            'Redis not available — skipping RedisOutboundQueue integration test'
        );

        return;
    }
    $redis->del([
        'tg_outbound:ready_keys',
        'tg_outbound:delayed',
        'tg_outbound:inflight',
        'tg-dlq:bot1',
        'tg-dlq:bot2',
        'tg_outbound:inflight:seq',
        'tg_outbound:global',
        'tg_outbound:global:delayed',
    ]);
    $queueKeys = $redis->keys('tg_outbound:q:*');
    if (is_array($queueKeys) && $queueKeys !== []) {
        $redis->del($queueKeys);
    }
    $delayedDataKeys = $redis->keys('tg_outbound:delayed:data:*');
    if (is_array($delayedDataKeys) && $delayedDataKeys !== []) {
        $redis->del($delayedDataKeys);
    }
    // The queue adapter consumes the RedisClientContract wrapper, not raw phpredis.
    $this->redis = new PhpRedisAdapter(new RedisDsn('127.0.0.1', 6379), $redis);
    $this->clock = new RedisTestClock();
});

describe('RedisOutboundQueue — push/pop/ack', function () {
    it('push then pop returns the task with a deliveryId', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $envelope = $queue->pop();

        expect($envelope)->not->toBeNull()
            ->and($envelope->task->id)->toBe('t1')
            ->and($envelope->deliveryId)->not->toBeNull();
    });

    it('pop returns null when the queue is empty', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        expect($queue->pop())->toBeNull();
    });

    it('ack removes the in-flight task', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $envelope = $queue->pop();

        $queue->ack($envelope);

        expect($queue->pop())->toBeNull()
            ->and($queue->size())->toBe(0);
    });

    it('size counts ready_keys + global + delayed', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $queue->push(makeRedisTask('t2'));

        expect($queue->size())->toBe(2);
    });
});

describe('RedisOutboundQueue — release / retry', function () {
    it('release with delay schedules the task for later', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $envelope = $queue->pop();

        $queue->release($envelope, delaySec: 100);

        expect($queue->pop())->toBeNull();
    });
});

describe('RedisOutboundQueue — Dead Letter Queue', function () {
    it('pushToDeadLetter stores and lists entries', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $envelope = new OutboundEnvelope(makeRedisTask('t1'), new OutboundTaskState());
        $entryId = $queue->pushToDeadLetter($envelope, 'bad_request');

        expect($entryId)->toBe('t1')
            ->and($queue->deadLetterSize())->toBe(1)
            ->and($queue->deadLetterSize('tg-dlq:bot1'))->toBe(1);

        $entries = $queue->listDeadLetter(null);
        expect($entries)->toHaveCount(1);
    });

    it('atomicFetchAndRemoveFromDlq extracts and deletes the entry', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $envelope = new OutboundEnvelope(makeRedisTask('t1'), new OutboundTaskState());
        $queue->pushToDeadLetter($envelope, 'expired');

        $json = $queue->atomicFetchAndRemoveFromDlq('tg-dlq:bot1', 't1');

        expect($json)->not->toBeNull()
            ->and($queue->deadLetterSize())->toBe(0);
    });

    it('getDlqChannels discovers DLQ channels by pattern', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->pushToDeadLetter(
            new OutboundEnvelope(makeRedisTask('t1', botId: 'bot1'), new OutboundTaskState()),
            'r'
        );
        $queue->pushToDeadLetter(
            new OutboundEnvelope(makeRedisTask('t2', botId: 'bot2'), new OutboundTaskState()),
            'r'
        );

        $channels = $queue->getDlqChannels('tg-dlq:*');

        expect($channels)->toContain('tg-dlq:bot1')
            ->and($channels)->toContain('tg-dlq:bot2');
    });
});

describe('RedisOutboundQueue — pressure lanes (06 §44)', function () {
    it('popWithLaneFloor skips below-floor lanes and keeps them queued', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('low', priority: TaskPriority::Low));
        $queue->push(makeRedisTask('normal', priority: TaskPriority::Normal));

        $popped = $queue->popWithLaneFloor(60, TaskPriority::Normal);
        expect($popped?->task->id)->toBe('normal')
            ->and($queue->size())->toBe(1)
            ->and($queue->pop()?->task->id)->toBe('low');
    });

    it('popWithLaneFloor with Low floor drains everything in score order', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('n1', priority: TaskPriority::Normal));
        $queue->push(makeRedisTask('c1', priority: TaskPriority::Critical));

        expect($queue->popWithLaneFloor(60, TaskPriority::Low)?->task->id)->toBe('c1')
            ->and($queue->popWithLaneFloor(60, TaskPriority::Low)?->task->id)->toBe('n1');
    });

    it('ack refreshes the lane head score so the ordering key stays discoverable', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1', priority: TaskPriority::Normal, orderingKey: 'chat:aging'));
        $queue->push(makeRedisTask('t2', priority: TaskPriority::Normal, orderingKey: 'chat:aging'));

        $first = $queue->pop();
        $queue->ack($first);

        expect($queue->lockNextReadyKey())->toBe('chat:aging');
    });
});

describe('RedisOutboundQueue — sustained load soak (06 §44–§46)', function () {
    it('drains 200 mixed-lane tasks with zero loss under floor gating', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);
        $total = 200;
        $ids = [];

        foreach (TaskPriority::cases() as $priority) {
            for ($i = 0; $i < $total / 4; $i++) {
                $id = $priority->name.'-'.$i;
                $queue->push(makeRedisTask($id, priority: $priority));
                $ids[] = $id;
            }
        }

        expect($queue->size())->toBe($total);

        // Simulate pressure: only Critical/High may leave while we drain them.
        $drained = [];
        while (count($drained) < $total && ($envelope = $queue->popWithLaneFloor(60, TaskPriority::High)) !== null) {
            $drained[] = $envelope->task->id;
            $queue->ack($envelope);
        }

        expect(count($drained))->toBe($total / 2); // Critical + High only

        // Pressure released: everything else drains, nothing lost or duplicated.
        $rest = [];
        while (($envelope = $queue->popWithLaneFloor(60, TaskPriority::Low)) !== null) {
            $rest[] = $envelope->task->id;
            $queue->ack($envelope);
        }

        expect(count($drained) + count($rest))->toBe($total)
            ->and(count(array_unique(array_merge($drained, $rest))))->toBe($total)
            ->and($queue->size())->toBe(0);
    });

    it('promotes an aged Low task across the Normal lane floor via Lua rescoring', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);

        $queue->push(makeRedisTaskOnClock($this->clock, 'aged-low', TaskPriority::Low));

        // Fresh Low must stay parked while Normal lane is paused.
        expect($queue->popWithLaneFloor(60, TaskPriority::Normal))->toBeNull();

        // After one laneCrossSec (900s) the aged Low scores 1e10 — exactly
        // the fresh Normal floor — and becomes eligible without any pressure
        // release: aging, not operator action, restores fairness.
        $this->clock->advance(900);

        $envelope = $queue->popWithLaneFloor(60, TaskPriority::Normal);
        expect($envelope?->task->id)->toBe('aged-low');
    });

    it('sustains four interleaved push/drain waves with zero loss and eventual aging fairness', function () {
        $queue = new RedisOutboundQueue($this->redis, $this->clock);
        $waves = 4;
        $perWave = 48;
        $delivered = [];

        for ($wave = 0; $wave < $waves; $wave++) {
            foreach (TaskPriority::cases() as $priority) {
                for ($i = 0; $i < $perWave / 4; $i++) {
                    $queue->push(makeRedisTaskOnClock($this->clock, "w{$wave}-{$priority->name}-{$i}", $priority));
                }
            }

            expect($queue->size())->toBe(($wave + 1) * $perWave - count($delivered));

            // Sustained pressure: drain only High+ lanes; clock advances so
            // earlier waves keep aging across floors between waves.
            while (($envelope = $queue->popWithLaneFloor(60, TaskPriority::High)) !== null) {
                $delivered[] = $envelope->task->id;
                $queue->ack($envelope);
            }

            $this->clock->advance(450);
        }

        // Pressure released: whatever remains drains with no floor.
        while (($envelope = $queue->popWithLaneFloor(60, TaskPriority::Low)) !== null) {
            $delivered[] = $envelope->task->id;
            $queue->ack($envelope);
        }

        $total = $waves * $perWave;

        expect(count($delivered))->toBe($total)
            ->and(count(array_unique($delivered)))->toBe($total)
            ->and($queue->size())->toBe(0)
            ->and($this->redis->lLen('tg-dlq:bot1'))->toBe(0)
            ->and($this->redis->zCard('tg_outbound:delayed'))->toBe(0);
    });
});

describe('RedisOutboundQueue — ordering (OutboundOrderingQueueContract)', function () {
    $flags = [true, false];

    foreach ($flags as $useLua) {
        describe(
            $useLua ? 'with Lua optimization' : 'without Lua optimization (PHP native)',
            function () use ($useLua): void {
                beforeEach(function () use ($useLua): void {
                    $this->queue = new RedisOutboundQueue(
                        $this->redis,
                        $this->clock,
                        $useLua
                    );
                });

                it('lockNextReadyKey returns null when no keys are ready', function (): void {
                    expect($this->queue->lockNextReadyKey())->toBeNull();
                });

                it('lockNextReadyKey returns a key after push with orderingKey', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));

                    expect($this->queue->lockNextReadyKey())->toBe('chat:1');
                });

                it('lockNextReadyKey returns null after key is consumed by pop', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $this->queue->pop();

                    expect($this->queue->lockNextReadyKey())->toBeNull();
                });

                it('refreshKeyState returns key to ready_keys when queue has more tasks', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $this->queue->push(makeRedisTask('t2', orderingKey: 'chat:1'));

                    $first = $this->queue->pop();
                    expect($first->task->id)->toBe('t1');

                    $this->queue->ack($first);

                    expect($this->queue->lockNextReadyKey())->toBe('chat:1');
                });

                it('refreshKeyState does not return key when queue is empty', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $first = $this->queue->pop();
                    $this->queue->ack($first);

                    expect($this->queue->lockNextReadyKey())->toBeNull();
                });

                it('different keys can be popped concurrently', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $this->queue->push(makeRedisTask('t2', orderingKey: 'chat:2'));

                    expect($this->queue->pop())->not->toBeNull();
                    expect($this->queue->pop())->not->toBeNull();
                    expect($this->queue->pop())->toBeNull();
                });

                it('broadcast task is poppable even when an ordering key is locked', function (): void {
                    $this->queue->push(makeRedisTask('ordered', orderingKey: 'chat:1'));
                    $this->queue->push(makeRedisTask('broadcast'));

                    $this->queue->pop();

                    expect($this->queue->pop()->task->id)->toBe('broadcast');
                });

                it('priority across keys — higher priority key is popped first', function (): void {
                    $this->queue->push(makeRedisTask('normal', orderingKey: 'chat:1', priority: TaskPriority::Normal));
                    $this->queue->push(makeRedisTask('high', orderingKey: 'chat:2', priority: TaskPriority::High));

                    expect($this->queue->pop()->task->id)->toBe('high');
                    expect($this->queue->pop()->task->id)->toBe('normal');
                });

                it('release with delay keeps key unavailable', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $envelope = $this->queue->pop();
                    $this->queue->release($envelope, delaySec: 100);

                    expect($this->queue->lockNextReadyKey())->toBeNull();
                });

                it('release with delay=0 re-adds key to ready_keys', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $envelope = $this->queue->pop();
                    $this->queue->release($envelope, delaySec: 0);

                    expect($this->queue->lockNextReadyKey())->toBe('chat:1');
                });

                it('expired inflight reclaim puts key back to ready_keys', function (): void {
                    $this->queue->push(makeRedisTask('t1', orderingKey: 'chat:1'));
                    $this->queue->pop(60);

                    $this->clock->advance(61);

                    expect($this->queue->reclaimExpired())->toBe(1);
                    expect($this->queue->lockNextReadyKey())->toBe('chat:1');
                });
            }
        );
    }
});
