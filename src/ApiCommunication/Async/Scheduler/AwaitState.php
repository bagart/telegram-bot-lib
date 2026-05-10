<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Scheduler;

/**
 * @internal
 */
final class AwaitState
{
    private bool $settled = false;
    private mixed $value = null;
    private ?\Throwable $exception = null;

    public function resolve(mixed $value): void
    {
        $this->settled = true;
        $this->value = $value;
    }

    public function reject(\Throwable $exception): void
    {
        $this->settled = true;
        $this->exception = $exception;
    }

    public function isSettled(): bool
    {
        return $this->settled;
    }

    public function unwrap(): mixed
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->value;
    }
}
