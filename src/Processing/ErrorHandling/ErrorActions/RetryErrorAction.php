<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingErrorActionContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;

final class RetryErrorAction implements ProcessingErrorActionContract
{
    public function __construct(
        private readonly ASKSchedulerContract $scheduler,
        private readonly int $delayMs = 1000,
        private readonly int $maxAttempts = 3,
    ) {
    }

    public function supports(\Throwable $e): bool
    {
        return true;
    }

    public function execute(ProcessorErrorContext $ctx): void
    {
        if ($ctx->attempt >= $this->maxAttempts) {
            return;
        }

        $delaySeconds = (int) ($this->delayMs / 1000);

        $nextCtx = $ctx->nextAttempt();

        $this->scheduler->sleep(
            function () use ($nextCtx): void {
                $nextCtx->processor->process(
                    $nextCtx->dto,
                    $nextCtx->botConfig,
                    $nextCtx->action,
                );
            },
            $delaySeconds,
        );
    }
}
