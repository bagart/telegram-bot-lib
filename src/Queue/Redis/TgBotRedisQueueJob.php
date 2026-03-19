<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Queue\Redis;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\TelegramBot\Contracts\Queue\JobHandlerFactoryContract;
use BAGArt\TelegramBot\Contracts\Queue\TgBotQueueJobContract;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\RedisQueueDTOProcessJob;
use BAGArt\TelegramBot\Queue\QueueJobEnvelope;

final class TgBotRedisQueueJob implements TgBotQueueJobContract
{
    private bool $deleted = false;

    private bool $released = false;

    private bool $failed = false;

    private int $attempts = 1;

    private ?JobHandlerFactoryContract $handlerFactory = null;

    private ?ASKQueueAdapterContract $queueAdapter = null;

    public function __construct(
        private readonly string $rawBody,
        private readonly string $queueName,
        ?ASKQueueAdapterContract $queueAdapter = null,
    ) {
        $this->queueAdapter = $queueAdapter;
    }

    public function setHandlerFactory(JobHandlerFactoryContract $factory): void
    {
        $this->handlerFactory = $factory;
    }

    public function setQueueAdapter(ASKQueueAdapterContract $adapter): void
    {
        $this->queueAdapter = $adapter;
    }

    public function fire(): void
    {
        if ($this->handlerFactory !== null) {
            $this->fireViaFactory();

            return;
        }

        $this->fireLegacy();
    }

    private function fireViaFactory(): void
    {
        $envelope = unserialize($this->rawBody, [
            'allowed_classes' => [QueueJobEnvelope::class],
        ]);

        if ($envelope instanceof QueueJobEnvelope) {
            $handler = $this->handlerFactory->build($envelope->handlerClass, $envelope->handlerParams);
            $handler->handle();

            return;
        }

        // Envelope not found — try legacy as fallback
        $this->fireLegacy();
    }

    private function fireLegacy(): void
    {
        $job = unserialize($this->rawBody, [
            'allowed_classes' => [RedisQueueDTOProcessJob::class],
        ]);

        if ($job instanceof RedisQueueDTOProcessJob) {
            $job->handle();
        }
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function release(int $delay = 0): void
    {
        if ($this->queueAdapter !== null) {
            if ($delay > 0) {
                $this->queueAdapter->pushDelayed($this->queueName, $this->rawBody, $delay + time());
            } else {
                $this->queueAdapter->push($this->queueName, $this->rawBody);
            }
        }

        $this->released = true;
    }

    public function isReleased(): bool
    {
        return $this->released;
    }

    public function isDeletedOrReleased(): bool
    {
        return $this->deleted || $this->released;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function maxTries(): ?int
    {
        return null;
    }

    public function maxExceptions(): ?int
    {
        return null;
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }

    public function markAsFailed(): void
    {
        $this->failed = true;
    }

    public function fail(?\Throwable $e = null): void
    {
        $this->failed = true;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    public function payload(): array
    {
        return [
            'data' => $this->rawBody,
            'displayName' => $this->resolveName(),
        ];
    }

    public function getName(): string
    {
        return $this->resolveName();
    }

    public function resolveName(): string
    {
        $data = @unserialize($this->rawBody, ['allowed_classes' => false]);

        if (is_object($data)) {
            return $data::class;
        }

        return 'TgBotRedisQueueJob';
    }

    public function uuid(): ?string
    {
        return null;
    }

    public function getJobId(): string
    {
        return hash('xxh3', $this->rawBody);
    }

    public function getConnectionName(): string
    {
        return 'redis';
    }

    public function getQueue(): string
    {
        return $this->queueName;
    }

    public function timeout(): ?int
    {
        return null;
    }

    public function retryUntil(): ?int
    {
        return null;
    }
}
