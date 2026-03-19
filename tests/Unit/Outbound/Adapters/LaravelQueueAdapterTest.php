<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\Adapters\LaravelQueueAdapter;
use BAGArt\TelegramBot\Outbound\Adapters\OutboundLaravelJob;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\TaskPriority;
use Illuminate\Contracts\Queue\Job as LaravelJobContract;
use Illuminate\Contracts\Queue\Queue as LaravelQueueContract;

/**
 * Hand-rolled fake Laravel Queue + Job (no Mockery, project convention).
 * Mimics Laravel behavior: push serializes marker-job, pop returns Job
 * with getRawBody = standard envelope {data:{command:serialized}}.
 */
class FakeLaravelQueue implements LaravelQueueContract
{
    /** @var list<array{job: OutboundLaravelJob, queue: string, delay: int}> */
    public array $pushed = [];

    public int $sizeReturn = 0;

    private int $seq = 0;

    public function push($job, $data = '', $queue = null)
    {
        $this->pushed[] = ['job' => $job, 'queue' => $queue, 'delay' => 0];
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        $this->pushed[] = ['job' => $job, 'queue' => $queue, 'delay' => (int)$delay];
    }

    public function pop($queue = null)
    {
        foreach ($this->pushed as $i => $entry) {
            if ($entry['queue'] === $queue && $entry['delay'] === 0) {
                unset($this->pushed[$i]);
                $this->pushed = array_values($this->pushed);

                return new FakeLaravelJob($entry['job'], (string)(++$this->seq));
            }
        }

        return null;
    }

    public function size($queue = null)
    {
        return $this->sizeReturn;
    }

    // Remaining contract methods — stubs (not used in LaravelQueueAdapter).
    public function pushOn($queue, $job, $data = '')
    {
    }
    public function pushRaw($payload, $queue = null, array $options = [])
    {
    }
    public function laterOn($queue, $delay, $job, $data = '')
    {
    }
    public function bulk($jobs, $data = '', $queue = null)
    {
    }
    public function getConnectionName()
    {
        return 'default';
    }
    public function setConnectionName($name)
    {
        return $this;
    }
    public function pendingSize($queue = null)
    {
        return 0;
    }
    public function delayedSize($queue = null)
    {
        return 0;
    }
    public function reservedSize($queue = null)
    {
        return 0;
    }
    public function creationTimeOfOldestPendingJob($queue = null)
    {
        return null;
    }
}

class FakeLaravelJob implements LaravelJobContract
{
    public function __construct(
        private readonly OutboundLaravelJob $marker,
        private readonly string $jobId,
    ) {
    }

    public function getJobId()
    {
        return $this->jobId;
    }

    public function getRawBody()
    {
        // Standard Laravel envelope: {data: {command: serialized marker}}.
        return json_encode([
            'data' => ['command' => serialize($this->marker)],
        ]);
    }

    public function delete()
    {
    }
    public function release($delay = 0)
    {
    }
    public function attempts()
    {
        return 1;
    }
    public function uuid()
    {
        return $this->jobId;
    }
    public function payload()
    {
        return [];
    }
    public function fire()
    {
    }
    public function isReleased()
    {
        return false;
    }
    public function isDeleted()
    {
        return false;
    }
    public function isDeletedOrReleased()
    {
        return false;
    }
    public function hasFailed()
    {
        return false;
    }
    public function markAsFailed()
    {
    }
    public function fail($e = null)
    {
    }
    public function maxTries()
    {
        return null;
    }
    public function maxExceptions()
    {
        return null;
    }
    public function timeout()
    {
        return null;
    }
    public function retryUntil()
    {
        return null;
    }
    public function getName()
    {
        return 'FakeLaravelJob';
    }
    public function resolveName()
    {
        return 'FakeLaravelJob';
    }
    public function resolveQueuedJobClass()
    {
        return FakeLaravelJob::class;
    }
    public function getConnectionName()
    {
        return 'default';
    }
    public function getQueue()
    {
        return 'tg-outbound';
    }
}

function makeLaravelTask(string $id = 't1'): OutboundTask
{
    return new OutboundTask(
        id: $id,
        botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
        dtoClass: 'App\\SendMessage',
        dtoData: ['chat_id' => 1, 'text' => 'hi'],
        priority: TaskPriority::Normal,
    );
}

describe('LaravelQueueAdapter', function () {
    it('push delegates to Laravel Queue::push with a marker job', function () {
        $fake = new FakeLaravelQueue();
        $adapter = new LaravelQueueAdapter($fake);

        $adapter->push(makeLaravelTask('t1'));

        expect($fake->pushed)->toHaveCount(1)
            ->and($fake->pushed[0]['job'])->toBeInstanceOf(OutboundLaravelJob::class)
            ->and($fake->pushed[0]['queue'])->toBe('tg-outbound');
    });

    it('pop extracts the payload from the marker job and sets deliveryId', function () {
        $fake = new FakeLaravelQueue();
        $adapter = new LaravelQueueAdapter($fake);

        $adapter->push(makeLaravelTask('t1'));
        $envelope = $adapter->pop();

        expect($envelope)->not->toBeNull()
            ->and($envelope->task->id)->toBe('t1')
            ->and($envelope->deliveryId)->not->toBeNull();
    });

    it('pop returns null when the queue is empty', function () {
        $adapter = new LaravelQueueAdapter(new FakeLaravelQueue());

        expect($adapter->pop())->toBeNull();
    });

    it('size delegates to Laravel Queue::size', function () {
        $fake = new FakeLaravelQueue();
        $fake->sizeReturn = 5;
        $adapter = new LaravelQueueAdapter($fake);

        expect($adapter->size())->toBe(5);
    });

    it('implements only OutboundQueueContract (no capabilities)', function () {
        $adapter = new LaravelQueueAdapter(new FakeLaravelQueue());

        expect($adapter)->toBeInstanceOf(BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract::class)
            ->and($adapter)->not->toBeInstanceOf(BAGArt\TelegramBot\Contracts\Outbound\LeaseRenewableQueueContract::class)
            ->and($adapter)->not->toBeInstanceOf(BAGArt\TelegramBot\Contracts\Outbound\AtomicDlqQueueContract::class);
    });

    it('release with delay re-pushes via later()', function () {
        $fake = new FakeLaravelQueue();
        $adapter = new LaravelQueueAdapter($fake);

        $adapter->push(makeLaravelTask('t1'));
        $envelope = $adapter->pop();
        $adapter->release($envelope, delaySec: 30);

        expect($fake->pushed)->toHaveCount(1)
            ->and($fake->pushed[0]['delay'])->toBe(30);
    });
});
