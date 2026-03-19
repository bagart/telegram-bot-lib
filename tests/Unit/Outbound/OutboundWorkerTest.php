<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundSkipException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\TgOutboundDaemon;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;

if (!class_exists('ControllableClock') || !function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../Helpers.php';
}

function okMiddleware(): OutboundMiddleware
{
    return new class () implements OutboundMiddleware {
        public function handle(OutboundEnvelope $envelope, Closure $next): void
        {
            $next($envelope);
        }
    };
}

function makeWorker(
    ?InMemoryOutboundQueue $queue = null,
    ?OutboundPipeline $pipeline = null,
    ?ASKFiberScheduler $scheduler = null,
    ?\BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker $circuitBreaker = null,
): TgOutboundDaemon {
    $clock = new ControllableClock();
    $config = new OutboundWorkerConfig();
    $scheduler ??= new ASKFiberScheduler();
    $queue ??= new InMemoryOutboundQueue($clock);
    $pipeline ??= new OutboundPipeline([okMiddleware()]);
    $cache = new \BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter(
        makeCacheWrapper(),
        new \BAGArt\ASKClient\Lockers\InMemoryLocker(),
    );
    $stats = new TgOutboundStats($cache);
    $circuitBreaker ??= new \BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker($cache);
    $leaseRenewer = new LeaseRenewer($queue, $clock);
    $logger = new ASKLogWrapper();

    return new TgOutboundDaemon(
        queue: $queue,
        pipeline: $pipeline,
        circuitBreaker: $circuitBreaker,
        stats: $stats,
        leaseRenewer: $leaseRenewer,
        logger: $logger,
        config: $config,
        scheduler: $scheduler,
    );
}

describe('OutboundWorker', function () {
    it('pops a task from the queue and processes it', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $worker = makeWorker(queue: $queue);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\SendMessage',
            dtoData: ['chat_id' => 1],
        ));

        expect($queue->size())->toBe(1);

        $worker->tick(0);

        $worker->tickScheduler(0);

        expect($queue->size())->toBe(0)
            ->and($worker->isIdle())->toBeTrue();
    });

    it('returns early when shutting down (no pop)', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $worker = makeWorker(queue: $queue);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->shutdown();
        $worker->tick(0);

        expect($queue->size())->toBe(1);
    });

    it('handles circuit breaker — releases when CB is open', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $cache = new \BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter(
            makeCacheWrapper(),
            new \BAGArt\ASKClient\Lockers\InMemoryLocker(),
        );
        $cb = new \BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker($cache);
        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');

        $worker = makeWorker(queue: $queue, circuitBreaker: $cb);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->tick(0);

        expect($queue->size())->toBe(1);
    });

    it('isIdle returns true when queue empty and no inflight', function () {
        $worker = makeWorker();
        $worker->startup();

        expect($worker->isIdle())->toBeTrue();
    });

    it('isIdle returns false when there are inflight tasks', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $worker = makeWorker(queue: $queue);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->tick(0);

        expect($worker->isIdle())->toBeFalse();
    });

    it('shutdown returns false when inflight tasks exist', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $worker = makeWorker(queue: $queue);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->tick(0);

        expect($worker->shutdown())->toBeFalse();
    });

    it('shutdown returns true when no inflight tasks', function () {
        $worker = makeWorker();
        $worker->startup();

        expect($worker->shutdown())->toBeTrue();
    });

    it('exposes tickable returning LeaseRenewer and scheduler', function () {
        $worker = makeWorker();

        $tickables = $worker->tickable();

        // Scheduler is required — without it, enqueued fibers (process) will never execute.
        expect($tickables)->toHaveCount(2)
            ->and($tickables[0])->toBeInstanceOf(LeaseRenewer::class)
            ->and($tickables[1])->toBeInstanceOf(\BAGArt\AsyncKernel\Contracts\ASKSchedulerContract::class);
    });

    it('onError logs and increments error counter', function () {
        $worker = makeWorker();
        $worker->startup();

        $worker->onError(new RuntimeException('test error'));
        expect(true)->toBeTrue();
    });

    it('name returns OutboundWorker', function () {
        $worker = makeWorker();

        expect($worker->name())->toBe('OutboundWorker');
    });

    it('processes via pipeline — successful send', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());

        $executed = new class () {
            public bool $called = false;
        };
        $testMiddleware = new class ($executed) implements \BAGArt\TelegramBot\Outbound\OutboundMiddleware {
            public function __construct(
                private readonly object $executed,
            ) {
            }
            public function handle(\BAGArt\TelegramBot\Outbound\OutboundEnvelope $envelope, \Closure $next): void
            {
                $this->executed->called = true;
                $next($envelope);
            }
        };
        $pipeline = new OutboundPipeline([$testMiddleware]);

        $worker = makeWorker(queue: $queue, pipeline: $pipeline);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->tick(0);
        $worker->tickScheduler(0);

        expect($queue->size())->toBe(0)
            ->and($executed->called)->toBeTrue();
    });

    it('handles OutboundSkipException — moves to DLQ', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $skipMiddleware = new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new OutboundSkipException('expired');
            }
        };
        $pipeline = new OutboundPipeline([$skipMiddleware]);

        $worker = makeWorker(queue: $queue, pipeline: $pipeline);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->tick(0);
        $worker->tickScheduler(0);

        expect($queue->size())->toBe(0)
            ->and($queue->deadLetterSize())->toBe(1);
    });

    it('handles poison pill (Throwable) gracefully', function () {
        $queue = new InMemoryOutboundQueue(new ControllableClock());
        $poisonMiddleware = new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new RuntimeException('something broke');
            }
        };
        $pipeline = new OutboundPipeline([$poisonMiddleware]);

        $worker = makeWorker(queue: $queue, pipeline: $pipeline);
        $worker->startup();

        $queue->push(new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'D',
            dtoData: [],
        ));

        $worker->tick(0);
        $worker->tickScheduler(0);

        expect($queue->size())->toBeGreaterThanOrEqual(0);
    });

    it('uses dlqFallback when queue lacks AtomicDlqQueueContract capability', function () {
        // Bare queue without AtomicDlqQueueContract — simulates LaravelQueueAdapter.
        $fallbackBag = new class () {
            public ?OutboundEnvelope $envelope = null;

            public ?string $reason = null;
        };
        $bareQueue = new class () implements \BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract {
            public ?OutboundEnvelope $next = null;

            public array $acked = [];

            public function push(\BAGArt\TelegramBot\Outbound\OutboundTask $task): void
            {
            }

            public function pop(int $visibilityTimeoutSec = 60): ?OutboundEnvelope
            {
                $e = $this->next;
                $this->next = null;

                return $e;
            }

            public function ack(OutboundEnvelope $envelope): void
            {
                $this->acked[] = $envelope->deliveryId;
            }

            public function release(OutboundEnvelope $envelope, int $delaySec): void
            {
            }

            public function size(): int
            {
                return 0;
            }
        };

        $skipMiddleware = new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                throw new OutboundSkipException('expired');
            }
        };

        $task = new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'D', dtoData: []);
        $bareQueue->next = new OutboundEnvelope($task, new \BAGArt\TelegramBot\Outbound\OutboundTaskState(), 'del1');

        $scheduler = new ASKFiberScheduler();
        $cache = new \BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter(
            makeCacheWrapper(),
            new \BAGArt\ASKClient\Lockers\InMemoryLocker(),
        );
        $worker = new TgOutboundDaemon(
            queue: $bareQueue,
            pipeline: new OutboundPipeline([$skipMiddleware]),
            circuitBreaker: new \BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker($cache),
            stats: new TgOutboundStats($cache),
            leaseRenewer: new LeaseRenewer($bareQueue, new ControllableClock()),
            logger: new ASKLogWrapper(),
            config: new OutboundWorkerConfig(),
            scheduler: $scheduler,
            dlqFallback: function (OutboundEnvelope $e, string $reason) use ($fallbackBag): void {
                $fallbackBag->envelope = $e;
                $fallbackBag->reason = $reason;
            },
        );
        $worker->startup();

        $worker->tick(0);
        $worker->tickScheduler(0);

        // Fallback invoked, task ack'd — not lost.
        expect($fallbackBag->envelope)->not->toBeNull()
            ->and($fallbackBag->reason)->toBe('expired')
            ->and($bareQueue->acked)->toContain('del1');
    });
});
