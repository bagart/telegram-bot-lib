<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Queue;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;

interface TgBotQueueJobContract
{
    /**
     * Fire the job.
     */
    public function fire(): void;

    /**
     * Delete the job from the queue.
     */
    public function delete(): void;

    /**
     * Determine if the job has been deleted.
     */
    public function isDeleted(): bool;

    /**
     * Release the job back into the queue after (n) seconds.
     */
    public function release(int $delay = 0): void;

    /**
     * Determine if the job was released back into the queue.
     */
    public function isReleased(): bool;

    /**
     * Determine if the job has been deleted or released.
     */
    public function isDeletedOrReleased(): bool;

    /**
     * Get the number of times the job has been attempted.
     */
    public function attempts(): int;

    /**
     * Get the number of times to attempt a job.
     */
    public function maxTries(): ?int;

    /**
     * Get the maximum number of exceptions allowed.
     */
    public function maxExceptions(): ?int;

    /**
     * Determine if the job has been marked as a failure.
     */
    public function hasFailed(): bool;

    /**
     * Mark the job as "failed".
     */
    public function markAsFailed(): void;

    /**
     * Delete the job and call the "failed" method.
     */
    public function fail(?\Throwable $e = null): void;

    /**
     * Get the raw body string for the job.
     */
    public function getRawBody(): string;

    /**
     * Get the decoded body of the job.
     *
     * @return array
     */
    public function payload(): array;

    /**
     * Get the name of the queued job class.
     */
    public function getName(): string;

    /**
     * Get the resolved name of the queued job class.
     */
    public function resolveName(): string;

    /**
     * Get the UUID of the job.
     */
    public function uuid(): ?string;

    /**
     * Get the job identifier.
     */
    public function getJobId(): string;

    /**
     * Get the name of the connection the job belongs to.
     */
    public function getConnectionName(): string;

    /**
     * Get the name of the queue the job belongs to.
     */
    public function getQueue(): string;

    /**
     * Get the number of seconds the job can run.
     */
    public function timeout(): ?int;

    /**
     * Get the timestamp indicating when the job should timeout.
     */
    public function retryUntil(): ?int;

    /**
     * Inject the handler factory for building job handlers on fire().
     */
    public function setHandlerFactory(JobHandlerFactoryContract $factory): void;

    /**
     * Set the queue adapter for release/retry operations.
     */
    public function setQueueAdapter(ASKQueueAdapterContract $adapter): void;
}
