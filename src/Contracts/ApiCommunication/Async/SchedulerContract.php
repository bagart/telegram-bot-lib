<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\Async;

use Fiber;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Interface SchedulerContract
 *
 * Defines the contract for an asynchronous scheduler that manages Fibers.
 */
interface SchedulerContract
{
    public const string TYPE = 'undefined';

    /**
     * Adds a new task (Fiber) to the scheduler.
     *
     * @param  Fiber  $fiber  The Fiber to be scheduled.
     * @return void
     */
    public function enqueue(Fiber $fiber): void;

    /**
     * Await a Guzzle Promise within a Fiber context.
     * Suspends the current Fiber until the promise resolves or rejects.
     *
     * @param  PromiseInterface  $promise  The promise to await.
     * @return mixed  The resolved value.
     */
    public function await(PromiseInterface $promise): mixed;

    public function isIdle(): bool;

    public function unpark(Fiber $fiber): void;

    /**
     * Park the currently running fiber.
     *
     * The fiber is suspended and will NOT be re-enqueued
     * automatically by the scheduler. It can only be
     * resumed via unpark().
     *
     * Must be called inside Fiber context.
     */
    public function parkCurrentFiber(): void;

    public function acquireLock(string $key): bool;

    public function releaseLock(string $key): void;

    public function tick(): void;
}
