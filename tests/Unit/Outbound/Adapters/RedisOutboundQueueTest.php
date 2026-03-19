<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\Adapters\RedisOutboundQueueContractContractContractContract;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * RedisOutboundQueueContractContractContractContract integration test against a live Redis.
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
    if (!extension_loaded('redis')) {
        return null;
    }
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379, 2.0);
        if (!$redis->ping()) {
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

function makeRedisTask(
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

// Before each test: clean queue keys.
uses()->beforeEach(function () {
    $redis = connectTestRedis();
    if ($redis === null) {
        test()->skip('Redis not available — skipping RedisOutboundQueueContractContractContractContract integration test');

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
    $this->redis = $redis;
    $this->clock = new RedisTestClock();
});

describe('RedisOutboundQueueContractContractContractContract — push/pop/ack', function () {
    it('push then pop returns the task with a deliveryId', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $envelope = $queue->pop();

        expect($envelope)->not->toBeNull()
            ->and($envelope->task->id)->toBe('t1')
            ->and($envelope->deliveryId)->not->toBeNull();
    });

    it('pop returns null when the queue is empty', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        expect($queue->pop())->toBeNull();
    });

    it('ack removes the in-flight task', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $envelope = $queue->pop();

        $queue->ack($envelope);

        expect($queue->pop())->toBeNull()
            ->and($queue->size())->toBe(0);
    });

    it('size counts ready_keys + global + delayed', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $queue->push(makeRedisTask('t2'));

        expect($queue->size())->toBe(2);
    });
});

describe('RedisOutboundQueueContractContractContractContract — release / retry', function () {
    it('release with delay schedules the task for later', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $queue->push(makeRedisTask('t1'));
        $envelope = $queue->pop();

        $queue->release($envelope, delaySec: 100);

        expect($queue->pop())->toBeNull();
    });
});

describe('RedisOutboundQueueContractContractContractContract — Dead Letter Queue', function () {
    it('pushToDeadLetter stores and lists entries', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $envelope = new OutboundEnvelope(makeRedisTask('t1'), new OutboundTaskState());
        $entryId = $queue->pushToDeadLetter($envelope, 'bad_request');

        expect($entryId)->toBe('t1')
            ->and($queue->deadLetterSize())->toBe(1)
            ->and($queue->deadLetterSize('tg-dlq:bot1'))->toBe(1);

        $entries = $queue->listDeadLetter(null);
        expect($entries)->toHaveCount(1);
    });

    it('atomicFetchAndRemoveFromDlq extracts and deletes the entry', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $envelope = new OutboundEnvelope(makeRedisTask('t1'), new OutboundTaskState());
        $queue->pushToDeadLetter($envelope, 'expired');

        $json = $queue->atomicFetchAndRemoveFromDlq('tg-dlq:bot1', 't1');

        expect($json)->not->toBeNull()
            ->and($queue->deadLetterSize())->toBe(0);
    });

    it('getDlqChannels discovers DLQ channels by pattern', function () {
        $queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock);

        $queue->pushToDeadLetter(new OutboundEnvelope(makeRedisTask('t1', botId: 'bot1'), new OutboundTaskState()), 'r');
        $queue->pushToDeadLetter(new OutboundEnvelope(makeRedisTask('t2', botId: 'bot2'), new OutboundTaskState()), 'r');

        $channels = $queue->getDlqChannels('tg-dlq:*');

        expect($channels)->toContain('tg-dlq:bot1')
            ->and($channels)->toContain('tg-dlq:bot2');
    });
});

describe('RedisOutboundQueueContractContractContractContract — ordering (OutboundOrderingQueueContract)', function () {
    $flags = [true, false];

    foreach ($flags as $useLua) {
        describe(
            $useLua ? 'with Lua optimization' : 'without Lua optimization (PHP native)',
            function () use ($useLua): void {
                beforeEach(function () use ($useLua): void {
                    $this->queue = new RedisOutboundQueueContractContractContractContract($this->redis, $this->clock, $useLua);
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
