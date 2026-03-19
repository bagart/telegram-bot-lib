<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Execution;

use Closure;

//todo add scheduler and dispatch separately
final class OrderedExecutionCoordinator
{
    /** @var array<string,bool> */
    private array $running = [];

    /** @var Closure */
    private array $queues = [];

    public function enqueue(
        string $key,
        Closure $task,
    ): ?Closure {
        if (!isset($this->running[$key])) {
            $this->running[$key] = true;

            return function () use ($key, $task): void {
                try {
                    $task();
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
        while (!empty($this->queues[$key])) {
            $nextTask = array_shift($this->queues[$key]);
            $nextTask();
        }
        unset($this->running[$key]);
        unset($this->queues[$key]);
    }
}
