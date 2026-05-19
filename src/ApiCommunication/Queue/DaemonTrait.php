<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use SplObjectStorage;
use Throwable;

trait DaemonTrait
{
    protected const int IDLE_SLEEP_US = 200000;

    protected const int MIN_SLEEP_US = 1000;

    protected const int MAX_CONCURRENT_FIBERS = 500;

    private bool $shouldStop = false;

    private int $totalProcessed = 0;

    private int $totalErrors = 0;

    private int $concurrentFibers = 0;

    private SplObjectStorage $delayedUnpark;

    private SchedulerContract $scheduler;

    private ?TgBotLogWrapper $logger = null;

    abstract protected function getLogPrefix(): string;

    abstract protected function tryConsume(): mixed;

    abstract protected function dispatch(mixed $item): void;

    protected function onStart(): void {}

    private function initDaemon(SchedulerContract $scheduler, ?TgBotLogWrapper $logger = null): void
    {
        $this->scheduler = $scheduler;
        $this->logger = $logger;
        $this->delayedUnpark = new SplObjectStorage();
    }

    public function run(): void
    {
        $this->logInfo($this->getLogPrefix() . ' started');

        $this->onStart();

        $this->setupSignalHandlers();

        while (!$this->shouldStop) {
            try {
                $this->processDelayedUnparks();

                if ($this->concurrentFibers < static::MAX_CONCURRENT_FIBERS) {
                    $item = $this->tryConsume();

                    if ($item !== null) {
                        $this->dispatch($item);
                    }
                }

                $this->scheduler->tick();

                $this->adaptiveSleep();
            } catch (Throwable $e) {
                $this->logger?->error(
                    sprintf('Daemon loop error: %s', $e->getMessage()),
                    ['exception' => $e::class],
                );

                usleep(static::IDLE_SLEEP_US);
            }
        }

        $this->shutdown();
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    public function parkWithDelay(float $delaySeconds): void
    {
        $fiber = Fiber::getCurrent();

        if ($fiber === null) {
            usleep((int) ($delaySeconds * 1_000_000));

            return;
        }

        $this->delayedUnpark->attach(
            $fiber,
            microtime(true) + $delaySeconds,
        );

        $this->scheduler->parkCurrentFiber();
    }

    private function processDelayedUnparks(): void
    {
        if ($this->delayedUnpark->count() === 0) {
            return;
        }

        $now = microtime(true);
        $readyFibers = [];

        foreach ($this->delayedUnpark as $fiber) {
            $unparkAt = $this->delayedUnpark->getInfo();

            if ($now >= $unparkAt) {
                $readyFibers[] = $fiber;
            }
        }

        foreach ($readyFibers as $fiber) {
            $this->delayedUnpark->detach($fiber);

            try {
                $this->scheduler->unpark($fiber);
            } catch (Throwable) {
            }
        }
    }

    private function adaptiveSleep(): void
    {
        if (!$this->scheduler->isIdle() && $this->concurrentFibers > 0) {
            return;
        }

        if ($this->delayedUnpark->count() > 0) {
            $nextUnpark = null;

            foreach ($this->delayedUnpark as $fiber) {
                $time = $this->delayedUnpark->getInfo();

                if ($nextUnpark === null || $time < $nextUnpark) {
                    $nextUnpark = $time;
                }
            }

            if ($nextUnpark !== null) {
                $remainingUs = (int) (($nextUnpark - microtime(true)) * 1_000_000);

                if ($remainingUs > static::MIN_SLEEP_US) {
                    usleep(min($remainingUs, static::IDLE_SLEEP_US));
                }

                return;
            }
        }

        usleep(static::IDLE_SLEEP_US);
    }

    private function setupSignalHandlers(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_async_signals(true);

        $daemon = $this;

        pcntl_signal(SIGTERM, static function () use ($daemon): void {
            $daemon->logger?->info('Received SIGTERM, shutting down...');
            $daemon->stop();
        });

        pcntl_signal(SIGINT, static function () use ($daemon): void {
            $daemon->logger?->info('Received SIGINT, shutting down...');
            $daemon->stop();
        });
    }

    private function shutdown(): void
    {
        $this->logInfo(
            sprintf(
                'Daemon shutting down. Processed: %d, Errors: %d, Concurrent: %d',
                $this->totalProcessed,
                $this->totalErrors,
                $this->concurrentFibers,
            ),
        );

        $this->scheduler->drainUntilIdle();
    }

    private function logInfo(string $message): void
    {
        $this->logger?->info(
            sprintf('[%s] %s', $this->getLogPrefix(), $message),
        );
    }
}
