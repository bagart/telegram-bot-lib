<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\RedisQueueDTOProcessJob;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Exceptions\TgQueueException;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotRedisQueueWrapper;
use Fiber;
use Throwable;

final class TgRedisQueueProcessorDaemon
{
    use DaemonTrait;

    public function __construct(
        private readonly TgBotRedisQueueWrapper $queue,
        SchedulerContract $scheduler,
        ?TgBotLogWrapper $logger = null,
    ) {
        $this->initDaemon($scheduler, $logger);
    }

    protected function getLogPrefix(): string
    {
        return 'TgRedisQueueProcessorDaemon';
    }

    protected function onStart(): void
    {
        $this->queue->connect();
    }

    protected function tryConsume(): mixed
    {
        return $this->queue->consumeRaw();
    }

    protected function dispatch(mixed $item): void
    {
        if (!is_string($item)) {
            return;
        }

        ++$this->concurrentFibers;

        $daemon = $this;
        $payload = $item;

        $fiber = new Fiber(function () use ($payload, $daemon): void {
            try {
                $job = unserialize($payload, [
                    'allowed_classes' => [RedisQueueDTOProcessJob::class],
                ]);

                if (!$job instanceof RedisQueueDTOProcessJob) {
                    throw new TgQueueException(
                        'Invalid job payload: expected RedisQueueDTOProcessJob',
                    );
                }

                $job->handle();

                ++$daemon->totalProcessed;

                $daemon->logger?->debug(
                    sprintf(
                        'Job completed: processor=%s botId=%s',
                        $job->processor,
                        $job->botId,
                    ),
                );
            } catch (Throwable $e) {
                ++$daemon->totalErrors;

                $daemon->logger?->error(
                    sprintf('Job failed: error=%s', $e->getMessage()),
                    [
                        'exception' => $e::class,
                        'payload_preview' => substr($payload, 0, 200),
                    ],
                );
            } finally {
                --$daemon->concurrentFibers;
            }
        });

        $this->scheduler->enqueue($fiber);
    }
}
