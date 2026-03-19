<?php

declare(strict_types=1);

use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\AtomicDlqQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\ChannelDiscoverableQueueContract;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;

if (!class_exists('ControllableClock') || !function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../../Helpers.php';
}

/**
 * @return array{daemon: TgOutboundDaemon, stats: TgOutboundStats, queue: InMemoryOutboundQueue, sender: TgSenderContract}
 */
function makeOutboundComponentsForCli(): array
{
    $clock = new ControllableClock();
    $queue = new InMemoryOutboundQueue($clock);
    $cache = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());
    $stats = new TgOutboundStats($cache);

    $pipeline = new OutboundPipeline([
        new class () implements OutboundMiddleware {
            public function handle(OutboundEnvelope $envelope, Closure $next): void
            {
                $next($envelope);
            }
        },
    ]);

    $daemon = new \BAGArt\TelegramBot\Outbound\TgOutboundDaemon(
        queue: $queue,
        pipeline: $pipeline,
        circuitBreaker: new OutboundCircuitBreaker($cache),
        stats: $stats,
        leaseRenewer: new LeaseRenewer($queue, $clock),
        logger: new \BAGArt\AsyncKernel\Wrappers\ASKLogWrapper(),
        config: new OutboundWorkerConfig(),
        scheduler: new \BAGArt\AsyncKernel\Drivers\ASKFiberScheduler(),
    );

    $sender = Mockery::mock(\BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract::class);

    return [
        'daemon' => $daemon,
        'stats' => $stats,
        'queue' => $queue,
        'sender' => $sender,
    ];
}

describe('Outbound CLI', function () {
    describe('TgOutboundStats JSON output', function () {
        it('getState returns empty for fresh stats', function () {
            $components = makeOutboundComponentsForCli();
            $state = $components['stats']->getState();

            expect($state)->toBeArray();
            $json = json_encode($state, JSON_THROW_ON_ERROR);
            expect($json)->toBeString();
            $decoded = json_decode($json, true);
            expect($decoded)->toBeArray();
        });

        it('getGlobalMetrics returns empty between hours', function () {
            $components = makeOutboundComponentsForCli();
            $from = date('YmdH', time() - 7200);
            $to = date('YmdH');

            $metrics = $components['stats']->getGlobalMetrics($from, $to);

            expect($metrics)->toBeArray();
            $json = json_encode($metrics, JSON_THROW_ON_ERROR);
            expect($json)->toBeString();
        });

        it('getBotMetrics returns empty for unknown bot', function () {
            $components = makeOutboundComponentsForCli();
            $from = date('YmdH', time() - 7200);
            $to = date('YmdH');

            $metrics = $components['stats']->getBotMetrics('nonexistent_bot', $from, $to);

            expect($metrics)->toBeArray();
            $json = json_encode($metrics, JSON_THROW_ON_ERROR);
            expect($json)->toBeString();
        });

        it('getState serializes to valid JSON', function () {
            $components = makeOutboundComponentsForCli();
            $state = $components['stats']->getState();

            expect(json_encode($state, JSON_THROW_ON_ERROR))->toBeString();
        });
    });

    describe('DLQ JSON output', function () {
        it('listDeadLetter returns empty for fresh queue', function () {
            $components = makeOutboundComponentsForCli();
            $queue = $components['queue'];

            expect($queue instanceof AtomicDlqQueueContract)->toBeTrue();

            $entries = $queue->listDeadLetter(null, 50);
            expect($entries)->toBeArray()->toHaveCount(0);

            $json = json_encode($entries, JSON_THROW_ON_ERROR);
            expect($json)->toBeString();
        });

        it('pushToDeadLetter then list returns entry as JSON', function () {
            $components = makeOutboundComponentsForCli();
            $queue = $components['queue'];
            expect($queue instanceof AtomicDlqQueueContract)->toBeTrue();

            $task = new OutboundTask(id: 'dlq_test_1', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'Test', dtoData: []);
            $envelope = new OutboundEnvelope(task: $task, state: new OutboundTaskState());
            $entryId = $queue->pushToDeadLetter($envelope, 'test_reason');

            $entries = $queue->listDeadLetter(null, 50);
            expect($entries)->toHaveCount(1);

            $entry = $entries[0];
            expect($entry)->toBeInstanceOf(DeadLetterEntry::class)
                ->and($entry->id)->toBe('dlq_test_1')
                ->and($entry->reason)->toBe('test_reason')
                ->and($entry->canRedeliver())->toBeTrue();

            $json = json_encode($entry->jsonSerialize(), JSON_THROW_ON_ERROR);
            expect($json)->toBeString();
            $decoded = json_decode($json, true);
            expect($decoded['id'])->toBe('dlq_test_1')
                ->and($decoded['reason'])->toBe('test_reason');
        });

        it('atomicFetchAndRemoveFromDlq works with channel', function () {
            $components = makeOutboundComponentsForCli();
            $queue = $components['queue'];
            expect($queue instanceof AtomicDlqQueueContract && $queue instanceof ChannelDiscoverableQueueContract)->toBeTrue();

            $task = new OutboundTask(id: 'fetch_test', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot2'), dtoClass: 'Test', dtoData: []);
            $envelope = new OutboundEnvelope(task: $task, state: new OutboundTaskState());
            $entryId = $queue->pushToDeadLetter($envelope, 'error');

            $channels = $queue->getDlqChannels('tg-dlq:*');
            expect($channels)->toHaveCount(1);

            $channel = $channels[0];
            $raw = $queue->atomicFetchAndRemoveFromDlq($channel, $entryId);
            expect($raw)->toBeString();

            $decoded = json_decode($raw, true);
            expect($decoded)->toBeArray()
                ->and($decoded['id'])->toBe('fetch_test');

            // Verify removed
            $remaining = $queue->listDeadLetter(null, 50);
            expect($remaining)->toHaveCount(0);
        });
    });

    describe('Queue status JSON', function () {
        it('queue size is 0 for fresh queue', function () {
            $components = makeOutboundComponentsForCli();
            $size = $components['queue']->size();

            expect($size)->toBe(0);
            expect(json_encode(['size' => $size], JSON_THROW_ON_ERROR))->toBeString();
        });

        it('queue size reports after push', function () {
            $components = makeOutboundComponentsForCli();
            $queue = $components['queue'];

            $queue->push(new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'b1'), dtoClass: 'C', dtoData: []));

            expect($queue->size())->toBe(1);

            $json = json_encode(['queue_size' => $queue->size()], JSON_THROW_ON_ERROR);
            expect($json)->toBe('{"queue_size":1}');
        });

        it('DLQ size is 0 for fresh queue', function () {
            $components = makeOutboundComponentsForCli();
            $queue = $components['queue'];
            expect($queue instanceof AtomicDlqQueueContract)->toBeTrue();

            $size = $queue->deadLetterSize();
            expect($size)->toBe(0);

            $json = json_encode(['dlq_size' => $size], JSON_THROW_ON_ERROR);
            expect($json)->toBe('{"dlq_size":0}');
        });

        it('DLQ channels returns empty for fresh queue', function () {
            $components = makeOutboundComponentsForCli();
            $queue = $components['queue'];
            expect($queue instanceof ChannelDiscoverableQueueContract)->toBeTrue();

            $channels = $queue->getDlqChannels('tg-dlq:*');
            expect($channels)->toBeArray()->toHaveCount(0);
        });
    });

    describe('Circuit breaker JSON', function () {
        it('getState returns closed for fresh CB', function () {
            $cb = new OutboundCircuitBreaker(
                new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker()),
            );

            $state = $cb->getState('test_bot');
            expect($state->value)->toBe('closed');

            $json = json_encode(['state' => $state->value], JSON_THROW_ON_ERROR);
            expect($json)->toBe('{"state":"closed"}');
        });
    });

    describe('DeadLetterEntry JSON', function () {
        it('serializes and deserializes correctly', function () {
            $task = new OutboundTask(id: 'json_test', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot'), dtoClass: 'Test', dtoData: ['key' => 'val']);
            $state = new OutboundTaskState(status: 'pending', attempt: 1, lastError: 'err');
            $envelope = new OutboundEnvelope(task: $task, state: $state);

            $entry = DeadLetterEntry::fromEnvelope($envelope, 'test_failure');

            $serialized = $entry->jsonSerialize();
            expect($serialized['id'])->toBe('json_test')
                ->and($serialized['reason'])->toBe('test_failure')
                ->and($serialized['redeliveryCount'])->toBe(0);

            $restored = DeadLetterEntry::fromJson($serialized);
            expect($restored->id)->toBe('json_test')
                ->and($restored->reason)->toBe('test_failure')
                ->and($restored->redeliveryCount)->toBe(0)
                ->and($restored->canRedeliver())->toBeTrue();

            $json = json_encode($serialized, JSON_THROW_ON_ERROR);
            expect($json)->toBeString();
        });

        it('restoreEnvelope creates valid envelope', function () {
            $task = new OutboundTask(id: 'restore_test', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'SendMsg', dtoData: []);
            $state = new OutboundTaskState(status: 'business_error', attempt: 2, lastError: '400');
            $envelope = new OutboundEnvelope(task: $task, state: $state);

            $entry = DeadLetterEntry::fromEnvelope($envelope, 'bad_request');
            $restored = $entry->restoreEnvelope();

            expect($restored->task->id)->toBe('restore_test')
                ->and($restored->task->botConfig->botId)->toBe('bot1')
                ->and($restored->state->getAttempt())->toBe(0)
                ->and($restored->state->getStatus())->toBe('pending');
        });
    });
});
