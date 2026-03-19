<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use Illuminate\Contracts\Queue\Queue as LaravelQueueContract;

/**
 * Laravel Queue wrapper for {@see OutboundQueueContract} — **base contract only**.
 *
 * Does not implement capabilities (LeaseRenewable/AtomicDlq/ChannelDiscoverable/Purgeable):
 * Worker checks instanceof and uses fallback. DLQ — via Laravel failed_jobs
 * (at queue:work level), lease renewal — not needed (Laravel manages retry_after itself).
 *
 * Limitations (todo.md §5.3):
 *   - pop() = destructive: Laravel pop+delete. Visibility timeout is managed
 *     natively (queue:work --retry-after). Our $visibilityTimeoutSec is ignored.
 *   - priority/orderingKey: ignored (Laravel queue — FIFO, no priorities).
 *     For priorities/ordering use RedisOutboundQueueContractContractContractContract.
 *   - release(delay) delegates to $job->release($delay) — native delayed requeue.
 *
 * Pattern mirrors ASK LaravelQueueAdapter: marker-job {@see OutboundLaravelJob}
 * with payload as public string, extraction via unserialize(data.command)->payload.
 */
final class LaravelQueueAdapter implements OutboundQueueContract
{
    private const string QUEUE_NAME = 'tg-outbound';

    public function __construct(
        private readonly LaravelQueueContract $queue,
    ) {
    }

    public function push(OutboundTask $task): void
    {
        $envelope = new OutboundEnvelope($task, new OutboundTaskState());
        $payload = json_encode($envelope, JSON_THROW_ON_ERROR);

        // Marker-job passes through opaque payload; Laravel serializes it.
        $this->queue->push(new OutboundLaravelJob($payload), '', self::QUEUE_NAME);
    }

    public function pop(int $visibilityTimeoutSec = 60): ?OutboundEnvelope
    {
        // $visibilityTimeoutSec is ignored — Laravel manages via retry_after.
        $job = $this->queue->pop(self::QUEUE_NAME);
        if ($job === null) {
            return null;
        }

        // Extract payload from the Laravel envelope {data: {command: serialized}}.
        $raw = $job->getRawBody();
        $data = json_decode($raw, true);
        $command = unserialize($data['data']['command'], ['allowed_classes' => [OutboundLaravelJob::class]]);

        if (! $command instanceof OutboundLaravelJob) {
            // Foreign job in the queue — skip (ack via delete).
            $job->delete();

            return null;
        }

        $envelopeData = json_decode($command->payload, true);
        $envelope = OutboundEnvelope::fromJson((array) $envelopeData);
        $envelope->deliveryId = $job->getJobId();

        // Destructive pop: delete immediately (ack). Laravel does not provide a separate ack —
        // delete = ack. If processing fails, Laravel returns the task via retry_after.
        $job->delete();

        return $envelope;
    }

    public function ack(OutboundEnvelope $envelope): void
    {
        // Already deleted in pop() (destructive). No-op.
        // deliveryId = jobId, but the Job object is not retained (Laravel does not support
        // ack-after-pop — only delete-at-pop or release-after-pop).
    }

    public function release(OutboundEnvelope $envelope, int $delaySec): void
    {
        // Native Laravel requeue with delay. Requires a Job object, which we do not
        // retain after destructive pop. In the Laravel flow, retry is managed via
        // queue:work (exception in handler → release via retry_after).
        //
        // For explicit retry, either don't delete in pop() (but then no ack), or
        // re-push via later(). We use later() — push a new delayed task.
        if ($delaySec > 0) {
            $this->queue->later(
                $delaySec,
                new OutboundLaravelJob(json_encode($envelope, JSON_THROW_ON_ERROR)),
                '',
                self::QUEUE_NAME,
            );
        } else {
            $this->push($envelope->task);
        }
    }

    public function size(): int
    {
        return (int) $this->queue->size(self::QUEUE_NAME);
    }
}
