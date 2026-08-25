<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Execution;

use BAGArt\TelegramBot\Contracts\Processing\OrderedTaskContract;
use Closure;

// todo add scheduler and dispatch separately
final class OrderedExecutionCoordinator
{
    /** @var array<string,bool> */
    private array $running = [];

    /** @var array<string,list<OrderedTaskContract>> */
    private array $queues = [];

    /**
     * Runs the task immediately when its key is free; otherwise parks it and
     * returns null. When runnable, returns the completion-tracking closure
     * (scheduler glue: executes the task, then drains queued siblings).
     */
    public function enqueue(
        string $key,
        OrderedTaskContract $task,
    ): ?Closure {
        if (! isset($this->running[$key])) {
            $this->running[$key] = true;

            return function () use ($key, $task): void {
                try {
                    $task->execute();
                } finally {
                    $this->complete($key);
                }
            };
        }

        $this->queues[$key][] = $task;

        return null;
    }

    private function complete(
        string $key,
    ): void {
        while (! empty($this->queues[$key])) {
            $nextTask = array_shift($this->queues[$key]);
            if ($nextTask instanceof OrderedTaskContract) {
                $nextTask->execute();
            }
        }
        unset($this->running[$key]);
        unset($this->queues[$key]);
    }
}
