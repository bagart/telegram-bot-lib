<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\AsyncKernel\Config\PartitionConfig;
use BAGArt\ASKClient\Contracts\Queue\ActivePartitionsContract;
use BAGArt\ASKClient\Contracts\Queue\JobDeduplicatorContract;
use BAGArt\AsyncKernel\Contracts\MetricsContract;
use BAGArt\ASKClient\Contracts\Queue\PartitionStreamContract;
use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgPartitionSchedulerContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;
use Psr\Log\LoggerInterface;

final class TgRedisPartitionScheduler implements TgPartitionSchedulerContract
{
    public function __construct(
        private readonly PartitionStreamContract $stream,
        private readonly ActivePartitionsContract $activePartitions,
        private readonly JobDeduplicatorContract $dedup,
        private readonly ASKQueueAdapterContract $retryQueue,
        private readonly PartitionConfig $config,
        private readonly ?MetricsContract $metrics = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function enqueue(UpdateContext $context): ?TgAsyncJob
    {
        $jobId = $context->jobId !== ''
            ? $context->jobId
            : bin2hex(random_bytes(16));

        $partitionKey = $context->executionKey
            ? $this->normalizePartitionKey($context->executionKey, $context->processor)
            : null;

        if ($this->dedup->isPermanentlyProcessed($jobId)) {
            $this->metrics?->recordDedupHit();

            $this->logger?->warning(
                '[PartitionScheduler] permanently processed job rejected {jobId}',
                ['jobId' => $jobId],
            );

            return null;
        }

        if (!$this->dedup->tryMark($jobId)) {
            $this->metrics?->recordDedupHit();

            $this->logger?->warning(
                '[PartitionScheduler] duplicate job rejected {jobId}',
                ['jobId' => $jobId],
            );

            return null;
        }

        $payload = json_encode($context, JSON_THROW_ON_ERROR);

        if ($partitionKey !== null) {
            $this->dedup->tryMarkCompound($jobId, $partitionKey, $payload);
        }

        $job = new TgAsyncJob(
            jobId: $jobId,
            partitionKey: $partitionKey,
            context: $context,
            createdAt: time(),
            attempt: 0,
        );

        $this->backpressureEnqueue($job);

        return $job;
    }

    public function enqueueJob(TgAsyncJob $job): void
    {
        $partitionKey = $job->partitionKey ?? '_global';

        if ($partitionKey === '_global') {
            $this->stream->push('_global', $job);
            $this->activePartitions->markActive('_global', time());

            $this->logger?->debug(
                '[PartitionScheduler] enqueued global job {jobId}',
                ['jobId' => $job->jobId],
            );

            return;
        }

        if (!$this->activePartitions->isScheduled($partitionKey)) {
            $this->activePartitions->markActive($partitionKey, time());
        }

        $this->stream->push($partitionKey, $job);

        $this->logger?->debug(
            '[PartitionScheduler] enqueued job {jobId} to partition {key}',
            ['jobId' => $job->jobId, 'key' => $partitionKey],
        );
    }

    private function normalizePartitionKey(string $executionKey, string $processor): string
    {
        return $processor . '::' . $executionKey;
    }

    private function backpressureEnqueue(TgAsyncJob $job): void
    {
        $partitionKey = $job->partitionKey ?? '_global';

        $streamLen = $this->stream->length($partitionKey);
        $retryCount = $this->retryQueue->size($partitionKey);

        if ($streamLen >= $this->config->streamMaxLen || $retryCount > $this->config->backpressureRetryThreshold) {
            $delay = $this->config->backpressureDelaySeconds;

            $streamFactor = $streamLen > $this->config->streamMaxLen * 2 ? 3 : 1;
            $retryFactor = $retryCount > $this->config->backpressureRetryThreshold * 2 ? 2 : 1;

            $adaptiveDelay = $delay * $streamFactor * $retryFactor;

            $this->logger?->warning(
                '[PartitionScheduler] adaptive backpressure for {key}: stream={streamLen}, retry={retryCount}, delay={delay}s',
                [
                    'key' => $partitionKey,
                    'streamLen' => $streamLen,
                    'retryCount' => $retryCount,
                    'delay' => $adaptiveDelay,
                ],
            );

            $this->retryQueue->push(
                $job,
                time() + $adaptiveDelay,
                $job->context->executionKey,
            );

            return;
        }

        $this->enqueueJob($job);
    }
}
