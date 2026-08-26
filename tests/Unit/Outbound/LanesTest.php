<?php

declare(strict_types=1);

use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundShedPolicyContract;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\Shedding\ShedDecision;
use BAGArt\TelegramBot\Outbound\TaskPriority;
use BAGArt\TelegramBot\Outbound\TgOutboundDaemon;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;
use BAGArt\TelegramBot\Tests\Support\RecordingLaneQueue;

if (! class_exists('ControllableClock')) {
    require_once __DIR__.'/../../Helpers.php';
}

function laneTask(string $id, TaskPriority $priority, ?string $orderingKey = null, ?DateTimeImmutable $createdAt = null): OutboundTask
{
    return new OutboundTask(
        id: $id,
        botConfig: new TgBotConfig(token: '123456:'.str_repeat('b', 35), botId: 'lane-bot'),
        dtoClass: 'App\\SendMessage',
        dtoData: ['chat_id' => 1],
        priority: $priority,
        orderingKey: $orderingKey,
        createdAt: $createdAt ?? new DateTimeImmutable('@1000000'),
    );
}

describe('Lane fairness in InMemoryOutboundQueue', function () {
    it('pops an aged Low before a fresh Normal (anti-starvation)', function (): void {
        $clock = new ControllableClock(1_001_000);
        $queue = new InMemoryOutboundQueue($clock);

        // Low created at t=1000000; after 1000s of waiting with default policy
        // (cross one lane per 900s) it outranks a fresh Normal.
        $queue->push(laneTask('low', TaskPriority::Low, createdAt: new DateTimeImmutable('@1000000')));
        $clock->advance(100);
        $queue->push(laneTask('normal', TaskPriority::Normal, createdAt: new DateTimeImmutable('@'.$clock->time())));

        $first = $queue->pop();
        expect($first->task->id)->toBe('low');
    });

    it('pops fresh higher lanes first (strict order preserved for fresh tasks)', function (): void {
        $clock = new ControllableClock(1_000_000);
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(laneTask('low', TaskPriority::Low));
        $queue->push(laneTask('critical', TaskPriority::Critical));

        expect($queue->pop()->task->id)->toBe('critical')
            ->and($queue->pop()->task->id)->toBe('low');
    });

    it('popWithLaneFloor leaves below-floor tasks queued (zero loss)', function (): void {
        $clock = new ControllableClock(1_000_000);
        $queue = new InMemoryOutboundQueue($clock);

        $queue->push(laneTask('low', TaskPriority::Low));
        $queue->push(laneTask('normal', TaskPriority::Normal));
        $queue->push(laneTask('high', TaskPriority::High));

        $popped = [];
        while (($envelope = $queue->popWithLaneFloor(60, TaskPriority::High)) !== null) {
            $popped[] = $envelope->task->id;
        }

        expect($popped)->toBe(['high'])
            ->and($queue->size())->toBe(2)
            ->and($queue->pop()->task->id)->toBe('normal')
            ->and($queue->pop()->task->id)->toBe('low');
    });

    it('shed policy defers Low under watermark pressure without loss', function (): void {
        $clock = new ControllableClock(1_000_000);
        $shed = new class () implements OutboundShedPolicyContract {
            public function decide(
                TaskPriority $priority,
                int $size,
                ?int $maxSize,
            ): ShedDecision {
                return $priority === TaskPriority::Low
                    ? ShedDecision::Defer(10)
                    : ShedDecision::Accept();
            }
        };
        $queue = new InMemoryOutboundQueue($clock, shedPolicy: $shed);

        $queue->push(laneTask('low-deferred', TaskPriority::Low));
        $queue->push(laneTask('normal-accepted', TaskPriority::Normal));

        expect($queue->pop()->task->id)->toBe('normal-accepted')
            ->and($queue->size())->toBe(1); // low sits in delayed set

        $clock->advance(11);
        expect($queue->pop()->task->id)->toBe('low-deferred');
    });

    it('shed policy drops Low to DLQ with load_shed reason', function (): void {
        $clock = new ControllableClock(1_000_000);
        $shed = new class () implements OutboundShedPolicyContract {
            public function decide(
                TaskPriority $priority,
                int $size,
                ?int $maxSize,
            ): ShedDecision {
                return $priority === TaskPriority::Low
                    ? ShedDecision::Drop('load_shed')
                    : ShedDecision::Accept();
            }
        };
        $queue = new InMemoryOutboundQueue($clock, shedPolicy: $shed);

        $queue->push(laneTask('dropped', TaskPriority::Low));
        $queue->push(laneTask('kept', TaskPriority::Normal));

        expect($queue->deadLetterSize('tg-dlq:lane-bot'))->toBe(1)
            ->and($queue->listDeadLetter('tg-dlq:lane-bot')[0]->reason)->toBe('load_shed')
            ->and($queue->pop()->task->id)->toBe('kept');
    });
});

describe('Pressure lane mapping in TgOutboundDaemon', function () {
    function laneRecordingQueue(array &$calls): RecordingLaneQueue
    {
        return new RecordingLaneQueue($calls);
    }

    function laneWorker(RecordingLaneQueue $queue): TgOutboundDaemon
    {
        $cache = new KernelCacheAdapter(
            makeCacheWrapper(),
            new InMemoryLocker(),
        );

        return new TgOutboundDaemon(
            queue: $queue,
            pipeline: new OutboundPipeline([]),
            circuitBreaker: new OutboundCircuitBreaker($cache),
            stats: new TgOutboundStats($cache),
            leaseRenewer: new LeaseRenewer($queue, new ControllableClock()),
            logger: new ASKLogWrapper(),
            config: new OutboundWorkerConfig(),
            scheduler: new ASKFiberScheduler(),
        );
    }

    it('maps pressure bands to lane floors', function (): void {
        $calls = [];
        $worker = laneWorker(laneRecordingQueue($calls));

        foreach ([0, 69, 70, 85, 95] as $pressure) {
            $worker->tick($pressure);
        }

        expect($calls)->toBe([
            TaskPriority::Low,
            TaskPriority::Low,
            TaskPriority::Normal,
            TaskPriority::High,
            TaskPriority::Critical,
        ]);
    });
});
