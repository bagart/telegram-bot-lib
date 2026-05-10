<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Scheduler;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Async\TgLibAsyncTechnicalException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgApiException;
use BAGArt\TelegramBot\Exceptions\TgBotTechnicalException;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use SplQueue;
use Throwable;

/**
 * Critical rules:
 *
 * 1. One shared scheduler for whole bot runtime.
 * 2. No nested private event loops.
 * 3. Promise callbacks must NEVER resume fiber directly.
 * 4. Resume only via scheduler queue.
 * 5. drainUntilIdle() is mandatory for Telegram ACK safety.
 */
final class FiberScheduler implements SchedulerContract
{
    public const string TYPE = 'fiber';

    private const IDLE_SLEEP_NO_WORK = 200000;
    private const IDLE_SLEEP_LIGHT = 20000;
    private const MAX_PROMISE_DRAIN_ITERATIONS = 100;

    /**
     * @var SplQueue<Fiber>
     */
    private SplQueue $queue;

    /**
     * Fibers suspended because of await()
     *
     * @var array<int, Fiber>
     */
    private array $waitingFibers = [];

    /**
     * Explicitly parked fibers
     *
     * @var array<int, Fiber>
     */
    private array $parkedFibers = [];

    private array $locks = [];

    public function __construct(
        private readonly TgBotApiTransportContract $transport,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
        $this->queue = new SplQueue();
    }

    public function enqueue(Fiber $fiber): void
    {
        if ($fiber->isTerminated()) {
            return;
        }

        $this->queue->enqueue($fiber);
    }

    public function await(PromiseInterface $promise): mixed
    {
        $fiber = Fiber::getCurrent();

        if ($fiber === null) {
            throw new TgLibAsyncTechnicalException(
                'FiberScheduler::await() must be called inside Fiber context'
            );
        }

        $state = new AwaitState();
        $fiberId = spl_object_id($fiber);

        $this->waitingFibers[$fiberId] = $fiber;

        $promise->then(
            function (mixed $value) use (
                $state,
                $fiber,
                $fiberId
            ): void {
                if ($state->isSettled()) {
                    return;
                }

                $state->resolve($value);

                unset($this->waitingFibers[$fiberId]);

                /**
                 * CRITICAL:
                 * Never resume here directly.
                 * Only enqueue back into scheduler.
                 */
                if (!$fiber->isTerminated()) {
                    $this->queue->enqueue($fiber);
                }
            },
            function (mixed $reason) use (
                $state,
                $fiber,
                $fiberId
            ): void {
                if ($state->isSettled()) {
                    return;
                }

                $exception = $reason instanceof Throwable
                    ? $reason
                    : new TgApiException(
                        'FiberScheduler::await(): Promise rejected'
                    );

                $state->reject($exception);

                unset($this->waitingFibers[$fiberId]);

                /**
                 * Same rule here.
                 */
                if (!$fiber->isTerminated()) {
                    $this->queue->enqueue($fiber);
                }
            }
        );

        /**
         * Important:
         * Only lightweight progress.
         * No hidden nested event loop here.
         */
        $this->drainPromiseQueue();
        $this->transport->tick();
        $this->drainPromiseQueue();

        if ($state->isSettled()) {
            unset($this->waitingFibers[$fiberId]);

            return $state->unwrap();
        }

        Fiber::suspend();

        unset($this->waitingFibers[$fiberId]);

        if (!$state->isSettled()) {
            throw new TgBotTechnicalException(
                'Fiber resumed before promise resolution'
            );
        }

        return $state->unwrap();
    }

    public function tick(): void
    {
        $this->drainPromiseQueue();

        /**
         * Execute only currently available queue size.
         * Prevent infinite monopolization.
         */
        $iterations = $this->queue->count();

        while (
            $iterations-- > 0
            && !$this->queue->isEmpty()
        ) {
            /** @var Fiber $fiber */
            $fiber = $this->queue->dequeue();

            $this->executeFiber($fiber);
        }

        $this->transport->tick();
        $this->drainPromiseQueue();

        if ($this->shouldSleepHard()) {
            usleep(self::IDLE_SLEEP_NO_WORK);
            return;
        }

        if ($this->shouldSleepLight()) {
            usleep(self::IDLE_SLEEP_LIGHT);
        }
    }

    public function drainUntilIdle(): void
    {
        /**
         * This method is the core guarantee:
         * Telegram next fetch MUST NOT happen
         * before ALL current async processing ends.
         */

        while (!$this->isIdle()) {
            $this->tick();
        }
    }

    public function isIdle(): bool
    {
        return
            $this->queue->isEmpty()
            && empty($this->waitingFibers)
            && empty($this->parkedFibers)
            && !$this->transport->hasActiveHandles();
    }
    public function unpark(Fiber $fiber): void
    {
        if ($fiber->isTerminated()) {
            return;
        }

        $fiberId = spl_object_id($fiber);

        unset($this->parkedFibers[$fiberId]);

        $this->queue->enqueue($fiber);
    }

    public function parkCurrentFiber(): void
    {
        $fiber = Fiber::getCurrent();

        if ($fiber === null) {
            throw new TgLibAsyncTechnicalException(
                'parkCurrentFiber() must be called inside Fiber context'
            );
        }

        $fiberId = spl_object_id($fiber);

        $this->parkedFibers[$fiberId] = $fiber;

        Fiber::suspend();
    }

    public function acquireLock(string $key): bool
    {
        if (isset($this->locks[$key])) {
            return false;
        }

        $this->locks[$key] = true;
        return true;
    }

    public function releaseLock(string $key): void
    {
        unset($this->locks[$key]);
    }
    private function executeFiber(Fiber $fiber): void
    {
        $id = spl_object_id($fiber);

        if ($fiber->isTerminated()) {
            unset($this->waitingFibers[$id]);
            unset($this->parkedFibers[$id]);
            return;
        }

        try {
            if (!$fiber->isStarted()) {
                $fiber->start();
            } elseif ($fiber->isSuspended()) {
                /**
                 * Resume only if not promise-waiting
                 * and not intentionally parked.
                 */
                if (
                    !isset($this->waitingFibers[$id])
                    && !isset($this->parkedFibers[$id])
                ) {
                    $fiber->resume();
                }
            }

            /**
             * Requeue rules:
             *
             * - not terminated
             * - not await()-blocked
             * - not intentionally parked
             */
            if (
                !$fiber->isTerminated()
                && !isset($this->waitingFibers[$id])
                && !isset($this->parkedFibers[$id])
            ) {
                $this->queue->enqueue($fiber);
            }
        } catch (Throwable $e) {
            unset($this->waitingFibers[$id]);
            unset($this->parkedFibers[$id]);

            // Critical execution failures only.
            $this->logger?->error(
                'Fiber execution failed: ' . $e->getMessage(),
                [
                    'fiber_id' => $id,
                    'exception' => $e::class,
                ]
            );
        }
    }

    private function shouldSleepHard(): bool
    {
        return
            $this->queue->isEmpty()
            && empty($this->waitingFibers)
            && empty($this->parkedFibers)
            && !$this->transport->hasActiveHandles();
    }

    private function shouldSleepLight(): bool
    {
        return
            $this->queue->isEmpty()
            && empty($this->waitingFibers);
    }

    private function drainPromiseQueue(): void
    {
        $queue = Utils::queue();
        $iterations = 0;

        do {
            $queue->run();
            ++$iterations;
        } while (
            !$queue->isEmpty()
            && $iterations < self::MAX_PROMISE_DRAIN_ITERATIONS
        );
    }
}
