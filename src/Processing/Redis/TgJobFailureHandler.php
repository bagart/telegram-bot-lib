<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\ASKClient\Contracts\Queue\JobFailureHandlerContract;
use BAGArt\ASKClient\Contracts\Queue\JobStateStoreContract;
use BAGArt\ASKClient\Contracts\Queue\DeadLetterQueueContract;
use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\Job\AsyncJob;
use BAGArt\AsyncKernel\Job\JobLifetimePolicy;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgProcessingErrorLoggerContract;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgUserNotificationSenderContract;
use Psr\Log\LoggerInterface;
use Throwable;

final class TgJobFailureHandler implements JobFailureHandlerContract
{
    private const RETRY_QUEUE_NAME = 'tg_retry_queue';

    /** @var list<int> */
    private array $attemptHistory = [];

    /** @var list<string> */
    private array $partitionHistory = [];

    /** @var list<int> */
    private array $retryChain = [];

    /** @var list<string> */
    private array $workerIdHistory = [];

    public function __construct(
        private readonly JobStateStoreContract $stateStore,
        private readonly ASKQueueAdapterContract $retryQueue,
        private readonly JobLifetimePolicy $lifetimePolicy,
        private readonly ?DeadLetterQueueContract $deadLetterQueue = null,
        private readonly ?TgProcessingErrorLoggerContract $errorLogger = null,
        private readonly ?TgUserNotificationSenderContract $notifier = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function handle(AsyncJob $job, Throwable $e): void
    {
        /** @var TgAsyncJob $job */
        $nextAttempt = $job->attempt + 1;

        $this->attemptHistory[] = $nextAttempt;

        if ($job->partitionKey !== null) {
            $this->partitionHistory[] = $job->partitionKey;
        }

        $this->retryChain[] = time();

        $meta = $this->stateStore->getMeta($job->jobId);

        if ($meta !== null && isset($meta['workerId'])) {
            $this->workerIdHistory[] = $meta['workerId'];
        }

        $this->logger?->error(
            '[FailureHandler] traceId={jobId} partition={partition} attempt={attempt} worker={worker} failed: {msg}',
            [
                'jobId' => $job->jobId,
                'partition' => $job->partitionKey ?? '_global',
                'attempt' => $nextAttempt,
                'worker' => $meta['workerId'] ?? '?',
                'msg' => $e->getMessage(),
            ],
        );

        $this->errorLogger?->log($job, $e, $nextAttempt);

        if ($this->lifetimePolicy->shouldRetry($job, $e)) {
            $retryAt = $this->lifetimePolicy->nextRetryAt($job);

            $retryJob = new TgAsyncJob(
                jobId: $job->jobId,
                partitionKey: $job->partitionKey,
                context: $job->context,
                createdAt: $job->createdAt,
                attempt: $nextAttempt,
                retryAt: $retryAt,
            );

            $this->stateStore->markRetry(
                $job->jobId,
                $retryAt,
            );

            // Fix: use pushDelayed and serialize data
            $this->retryQueue->pushDelayed(
                self::RETRY_QUEUE_NAME,
                serialize($retryJob),
                $retryAt,
                $job->partitionKey,
            );

            $this->logger?->debug(
                '[FailureHandler] scheduled retry traceId={jobId} at {at}',
                ['jobId' => $job->jobId, 'at' => $retryAt],
            );
        } else {
            $this->stateStore->markDeadLetter($job->jobId, $e->getMessage());

            $history = [
                'attempts' => $this->attemptHistory,
                'partitions' => $this->partitionHistory,
                'retryChain' => $this->retryChain,
                'workerIds' => $this->workerIdHistory,
                'maxAttempts' => $this->lifetimePolicy->maxAttempts(),
            ];

            $this->deadLetterQueue?->push($job, $e, $history);
            $this->notifier?->notify($job, $e, $nextAttempt);

            $this->logger?->error(
                '[FailureHandler] job {jobId} exhausted retries -> DLQ (attempts: {attempts})',
                [
                    'jobId' => $job->jobId,
                    'attempts' => implode(',', $this->attemptHistory),
                ],
            );
        }
    }
}
