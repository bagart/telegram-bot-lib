<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\AsyncKernel\ASKShutdownContext;
use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKDaemonContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\Processing\UpdateRouterContract;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;
use Throwable;

final class ProcessorUpdateDaemon implements
    ASKDaemonContract,
    ASKTickableContract,
    WithASKTickableContract
{
    public function __construct(
        private readonly ASKQueueAdapterContract $queue,
        private readonly UpdateRouterContract $updateRouter,
        private readonly ASKLogWrapper $logger,
        private readonly string $queueName = 'tg-inbox',
        private readonly string $name = 'ProcessorUpdateDaemon',
        private readonly ?ASKSchedulerContract $processorScheduler = null,
    ) {
    }

    public function tick(int $systemPressure): void
    {
        $payload = $this->queue->pop($this->queueName);

        if ($payload === null) {
            return;
        }

        $updateContext = unserialize($payload, ['allowed_classes' => true]);
        assert($updateContext instanceof UpdateContext);

        $this->updateRouter->dispatch($updateContext);
    }

    public function pressure(): int
    {
        $size = $this->queue->size($this->queueName);

        if ($size === 0) {
            return 0;
        }

        return (int) round(($size / 256) * 100);
    }

    public function isIdle(): bool
    {
        return $this->queue->size($this->queueName) === 0;
    }

    public function queueSize(): int
    {
        return $this->queue->size($this->queueName);
    }

    public function onError(Throwable $e): void
    {
        throw $e;
    }

    public function startup(): void
    {
        $this->logger->info("ProcessorASKDaemon started");
    }

    public function name(): string
    {
        return $this->name;
    }

    public function shutdown(ASKShutdownContext $context): bool
    {
        foreach ($this->tickable() as $tickable) {
            if (!$tickable->isIdle()) {
                return false;
            }
        }

        return true;
    }

    public function tickable(): array
    {
        $tickable = [
            ...$this->updateRouter->tickable(),
            $this,
        ];

        if ($this->processorScheduler !== null) {
            $tickable[] = $this->processorScheduler;
        }

        return $tickable;
    }
}
