<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\Async;

use Fiber;

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
     * Runs the event loop until there are no more active tasks or it is stopped.
     *
     * @return void
     */
    public function run(): void;

    /**
     * Stops the event loop.
     *
     * @return void
     */
    public function stop(): void;

     /**
     * Returns the number of currently active fibers.
     *
     * @return int
     */
    public function getActiveCount(): int;

    /**
     * Register a fiber as waiting for I/O.
     */
    public function registerWaitingFiber(Fiber $fiber): void;

    /**
     * Unregister a fiber that was waiting for I/O.
     */
    public function unregisterWaitingFiber(Fiber $fiber): void;

    /**
     * Runs pending Guzzle promise tasks and advances the transport.
     * Called before Fiber::suspend() to ensure I/O handles are registered and executed.
     */
    public function preAwaitTick(): void;
}
