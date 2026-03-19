<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Queue;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\ASKShutdownContext;
use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKDaemonContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\Queue\JobHandlerFactoryContract;
use BAGArt\TelegramBot\Contracts\Queue\TgBotQueueJobContract;
use Fiber;
use Throwable;

final class ASKQueueDaemon implements ASKDaemonContract, WithASKTickableContract
{
    private int $concurrentFibers = 0;

    private int $totalProcessed = 0;

    private int $totalErrors = 0;

    private readonly string $name;

    public function __construct(
        private readonly ASKQueueAdapterContract $queue,
        private readonly ASKSchedulerContract $scheduler,
        private readonly ASKLogWrapper $logger,
        private readonly JobHandlerFactoryContract $handlerFactory,
        private readonly int $maxConcurrentFibers = 500,
        private readonly string $queueName = 'ask_queue',
        ?string $name = null,
    ) {
        $this->name = $name ?? substr(strrchr(self::class, '\\'), 1);
    }

    public function schedule(): void
    {
        if ($this->concurrentFibers >= $this->maxConcurrentFibers) {
            return;
        }

        $payload = $this->queue->pop($this->queueName);

        if ($payload === null) {
            return;
        }

        /** @var TgBotQueueJobContract $job */
        $job = unserialize($payload, ['allowed_classes' => [TgBotQueueJobContract::class]]);
        $job->setHandlerFactory($this->handlerFactory);
        $job->setQueueAdapter($this->queue);

        $this->dispatch($job);
    }

    public function onError(Throwable $e): void
    {
        $this->totalErrors++;

        $this->logger->error(
            sprintf('QueueDaemon error: %s', $e->getMessage()),
            ['exception' => $e::class],
        );
    }

    public function startup(): void
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function shutdown(ASKShutdownContext $context): bool
    {
        return $this->concurrentFibers === 0;
    }

    public function tickable(): array
    {
        return [$this->scheduler];
    }

    private function dispatch(TgBotQueueJobContract $job): void
    {
        $this->concurrentFibers++;

        $fiber = new Fiber(function () use ($job): void {
            try {
                $job->fire();

                $this->onJobSuccess($job);
            } catch (Throwable $e) {
                $this->onJobFailure($job, $e);
            } finally {
                $this->concurrentFibers--;
            }
        });

        $this->scheduler->enqueue($fiber);
    }

    private function onJobSuccess(TgBotQueueJobContract $job): void
    {
        $this->totalProcessed++;

        $this->logger->debug(
            sprintf(
                'QueueDaemon Job completed: job=%s',
                $job->resolveName(),
            ),
        );
    }

    private function onJobFailure(
        TgBotQueueJobContract $job,
        Throwable $e,
    ): void {
        $this->totalErrors++;

        $this->logger->error(
            sprintf(
                'QueueDaemon Job failed: job=%s error=%s',
                $job->resolveName(),
                $e->getMessage(),
            ),
            [
                'exception' => $e::class,
                'job_id' => $job->getJobId(),
                'payload_preview' => mb_substr($job->getRawBody(), 0, 200),
            ],
        );

        $job->fail($e);
    }
}
