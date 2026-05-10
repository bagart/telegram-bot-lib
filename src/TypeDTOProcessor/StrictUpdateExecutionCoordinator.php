<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use Throwable;

/**
 * Guarantees strict execution ordering for:
 *
 * (ProcessorClassName + chatId)
 *
 * Critical:
 * must use scheduler park/unpark API,
 * never raw Fiber::suspend() polling loops.
 */
final class StrictUpdateExecutionCoordinator
{
    /**
     * [processorClass][chatId] => last fiber in chain
     *
     * @var array<string, array<int|string, Fiber>>
     */
    private array $chains = [];

    /**
     * [processorClass][chatId] => next waiting fibers
     *
     * @var array<string, array<int|string, list<Fiber>>>
     */
    private array $waiters = [];

    public function __construct(
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    /**
     * Strict ordered execution:
     *
     * same (processor + chat)
     * executes strictly one-by-one
     */
    public function enqueue(
        string $processorClass,
        int|string $chatId,
        SchedulerContract $scheduler,
        callable $task,
    ): void {
        $previousFiber = $this->chains[$processorClass][$chatId] ?? null;

        $currentFiber = new Fiber(function () use (
            $previousFiber,
            $processorClass,
            $chatId,
            $scheduler,
            $task
        ): void {
            /**
             * Critical:
             * park instead of busy suspend loop
             */
            if (
                $previousFiber !== null
                && !$previousFiber->isTerminated()
            ) {
                $this->waiters[$processorClass][$chatId][] = Fiber::getCurrent();

                /**
                 * Scheduler must know
                 * this fiber is intentionally parked.
                 */
                $scheduler->parkCurrentFiber();
            }

            try {
                $task();
            } catch (Throwable $e) {
                $this->logger?->error(
                    'Strict processor execution failed',
                    [
                        'processor' => $processorClass,
                        'chat_id' => $chatId,
                        'exception' => $e::class,
                        'message' => $e->getMessage(),
                    ]
                );
            } finally {
                $this->releaseCurrentChainTail(
                    $processorClass,
                    $chatId,
                );

                /**
                 * Wake exactly one next waiter.
                 * True FIFO ordering.
                 */
                $this->resumeNextWaitingFiber(
                    $processorClass,
                    $chatId,
                    $scheduler,
                );
            }
        });

        /**
         * Replace chain tail
         */
        $this->chains[$processorClass][$chatId] = $currentFiber;

        $scheduler->enqueue($currentFiber);
    }

    public function hasPending(
        string $processorClass,
        int|string $chatId,
    ): bool {
        if (
            !isset($this->chains[$processorClass][$chatId])
        ) {
            return false;
        }

        $fiber = $this->chains[$processorClass][$chatId];

        return !$fiber->isTerminated();
    }

    public function cleanupTerminated(): void
    {
        foreach ($this->chains as $processorClass => $chatChains) {
            foreach ($chatChains as $chatId => $fiber) {
                if (!$fiber->isTerminated()) {
                    continue;
                }

                unset(
                    $this->chains[$processorClass][$chatId]
                );

                if (
                    empty(
                        $this->waiters[$processorClass][$chatId] ?? []
                    )
                ) {
                    unset(
                        $this->waiters[$processorClass][$chatId]
                    );
                }
            }

            if (empty($this->chains[$processorClass])) {
                unset(
                    $this->chains[$processorClass]
                );
            }

            if (empty($this->waiters[$processorClass] ?? [])) {
                unset(
                    $this->waiters[$processorClass]
                );
            }
        }
    }

    private function releaseCurrentChainTail(
        string $processorClass,
        int|string $chatId,
    ): void {
        if (
            isset($this->chains[$processorClass][$chatId])
            && $this->chains[$processorClass][$chatId] === Fiber::getCurrent()
        ) {
            unset(
                $this->chains[$processorClass][$chatId]
            );

            if (empty($this->chains[$processorClass])) {
                unset(
                    $this->chains[$processorClass]
                );
            }
        }
    }

    private function resumeNextWaitingFiber(
        string $processorClass,
        int|string $chatId,
        SchedulerContract $scheduler,
    ): void {
        if (
            empty(
                $this->waiters[$processorClass][$chatId] ?? []
            )
        ) {
            unset(
                $this->waiters[$processorClass][$chatId]
            );

            if (empty($this->waiters[$processorClass] ?? [])) {
                unset(
                    $this->waiters[$processorClass]
                );
            }

            return;
        }

        /**
         * FIFO: first waiter wakes first
         */
        $nextFiber = array_shift(
            $this->waiters[$processorClass][$chatId]
        );

        if ($nextFiber instanceof Fiber) {
            $scheduler->unpark($nextFiber);
        }

        if (
            empty(
                $this->waiters[$processorClass][$chatId]
            )
        ) {
            unset(
                $this->waiters[$processorClass][$chatId]
            );
        }

        if (empty($this->waiters[$processorClass] ?? [])) {
            unset(
                $this->waiters[$processorClass]
            );
        }
    }
}
