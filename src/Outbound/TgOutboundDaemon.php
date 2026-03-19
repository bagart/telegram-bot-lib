<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\AsyncKernel\ASKShutdownContext;
use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKDaemonContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\Outbound\AtomicDlqQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundOrderingQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use Closure;
use Fiber;
use Throwable;

final class TgOutboundDaemon implements ASKDaemonContract, ASKTickableContract, WithASKTickableContract
{
    private bool $isShuttingDown = false;

    /** @var array<string, OutboundEnvelope> */
    private array $inflight = [];

    private int $totalProcessed = 0;

    private int $totalErrors = 0;

    public function __construct(
        private readonly OutboundQueueContract $queue,
        private readonly OutboundPipeline $pipeline,
        private readonly OutboundCircuitBreakerContract $circuitBreaker,
        private readonly TgOutboundStats $stats,
        private readonly LeaseRenewer $leaseRenewer,
        private readonly ASKLogWrapper $logger,
        private readonly OutboundWorkerConfig $config,
        private readonly ASKSchedulerContract $scheduler,
        /**
         * DLQ fallback: called when the queue does NOT implement AtomicDlqQueueContract
         * (e.g. LaravelQueueAdapter). Signature: fn(OutboundEnvelope, string $reason): void.
         * null — the task is logged (md5+truncated) but not silently lost.
         */
        private readonly ?Closure $dlqFallback = null,
    ) {
        if (!$queue instanceof OutboundOrderingQueueContract) {
            $logger?->warning(
                '[OutboundWorker] Queue does not implement OutboundOrderingQueueContract — ordering guarantees are not enforced'
            );
        }
    }

    public function tick(int $systemPressure): void
    {
        if ($this->isShuttingDown) {
            return;
        }

        $envelope = $this->queue->pop($this->config->visibilityTimeoutSec);

        if ($envelope === null) {
            return;
        }
        $botId = $envelope->task->botConfig->botId;
        if (!$this->circuitBreaker->allowsRequest($botId)) {
            $this->queue->release($envelope, 30);
            $this->stats->recordRetry(
                botId: $botId,
                method: $envelope->task->dtoClass,
                reason: 'circuit_breaker',
            );

            return;
        }

        $this->inflight[$envelope->deliveryId] = $envelope;
        $this->leaseRenewer->track($envelope);

        $fiber = new Fiber(function () use ($envelope): void {
            try {
                $this->process($envelope);
            } finally {
                $this->leaseRenewer->untrack($envelope->deliveryId);
                unset($this->inflight[$envelope->deliveryId]);
            }
        });

        $this->scheduler->enqueue($fiber);
    }

    private function process(OutboundEnvelope $envelope): void
    {
        $envelope->state->incrementAttempt();
        $envelope->state->markInProgress();

        $botId = $envelope->task->botConfig->botId;
        try {
            $this->pipeline->execute($envelope);

            $this->queue->ack($envelope);
            $this->circuitBreaker->recordSuccess($botId);
            $this->stats->recordSent(
                botId: $botId,
                method: $envelope->task->dtoClass,
            );
            $this->totalProcessed++;
        } catch (OutboundSkipException $e) {
            $this->moveToDlq($envelope, $e->reason);
        } catch (OutboundBusinessErrorException $e) {
            $this->moveToDlq($envelope, $e->reason);
        } catch (OutboundRetryException $e) {
            $delay = $this->calculateDelay(
                attempt: $envelope->state->getAttempt(),
                delaySec: $e->delaySec,
            );
            $this->queue->release($envelope, $delay);
            $this->circuitBreaker->recordFailure($botId);
            $this->stats->recordRetry(
                botId: $botId,
                method: $envelope->task->dtoClass,
                reason: $e->reason,
            );
        } catch (Throwable $e) {
            $this->queue->release($envelope, 0);
            $this->stats->recordFailed(
                botId: $botId,
                method: $envelope->task->dtoClass,
                reason: 'fatal_worker_error',
            );
            $this->logger->error('Fatal worker error', [
                'error' => $e->getMessage(),
                'task_md5' => md5(json_encode($envelope->task)),
            ]);
        }
    }

    private function moveToDlq(OutboundEnvelope $envelope, string $reason): void
    {
        $this->queue->ack($envelope);
        $botId = $envelope->task->botConfig->botId;

        if ($this->queue instanceof AtomicDlqQueueContract) {
            $this->queue->pushToDeadLetter($envelope, $reason);
        } elseif ($this->dlqFallback !== null) {
            // Broker without AtomicDlqQueueContract (e.g. LaravelQueueAdapter) — fallback via callback.
            ($this->dlqFallback)($envelope, $reason);
        } else {
            // Neither AtomicDlqQueueContract nor fallback — do NOT silently lose: log as poison pill
            // (md5 + truncated 256, per todo.md §7.3 — full payload is not logged).
            $this->logger->error('[OutboundWorker] DLQ unavailable — task dropped to log', [
                'reason' => $reason,
                'task_md5' => md5(json_encode($envelope->task, JSON_THROW_ON_ERROR)),
                'task_preview' => substr(json_encode($envelope->task, JSON_THROW_ON_ERROR), 0, 256),
                'bot_id' => $botId,
            ]);
        }

        $this->stats->recordDlqPushed($botId);
        $this->stats->recordBusinessError(
            botId: $botId,
            method: $envelope->task->dtoClass,
            code: 400,
        );
    }

    /**
     * Exponential backoff with base delay, but not less than the provided delay.
     * Formula: max(delay, min(defaultRetryDelay * (attempt ^ 2), 300)).
     */
    private function calculateDelay(int $attempt, int $delaySec): int
    {
        $backoff = min($this->config->defaultRetryDelaySec * ($attempt ** 2), 300);

        return max($delaySec, $backoff);
    }

    public function tickable(): array
    {
        // Scheduler is required: tick() does pop + scheduler->enqueue(fiber), and the
        // fiber (process → pipeline → send) only executes when the kernel ticks the scheduler.
        // Without the scheduler, fibers will never run — tasks will stall in the queue.
        return [$this->leaseRenewer, $this->scheduler];
    }

    public function pressure(): int
    {
        $size = $this->queue->size();

        if ($size === 0) {
            return 0;
        }

        return (int)round(($size / 256) * 100);
    }

    public function isIdle(): bool
    {
        return (
            $this->inflight === []
            && $this->queue->size() === 0
            && $this->scheduler->isIdle()
        );
    }

    public function queueSize(): int
    {
        return $this->queue->size();
    }

    public function tickScheduler(int $systemPressure): void
    {
        $this->scheduler->tick($systemPressure);
    }

    public function onError(Throwable $e): void
    {
        $this->totalErrors++;

        $this->logger->error("[OutboundWorker] {$e->getMessage()}", [
            'exception' => $e::class,
        ]);
    }

    public function startup(): void
    {
        $this->logger->info('[OutboundWorker] started');
    }

    public function shutdown(ASKShutdownContext $context): bool
    {
        if (!$this->isShuttingDown) {
            $this->isShuttingDown = true;

            $this->logger->debug('[OutboundWorker::shutdown]: need to complete: ', [
                'count' => count($this->inflight),
            ]);
        }

        if ($this->inflight === []) {
            return true;
        }

        $this->logger->debug('[OutboundWorker] shutdown: waiting for in-flight tasks', [
            'count' => count($this->inflight),
        ]);

        return false;
    }

    public function name(): string
    {
        return 'OutboundWorker';
    }
}
