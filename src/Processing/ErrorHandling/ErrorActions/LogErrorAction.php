<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingErrorActionContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;

final class LogErrorAction implements ProcessingErrorActionContract
{
    public function __construct(
        private readonly ASKLogWrapper $logger,
    ) {
    }

    public function supports(\Throwable $e): bool
    {
        return true;
    }

    public function execute(ProcessorErrorContext $ctx): void
    {
        $this->logger->error(
            '[' . $ctx->processor::class . '] ' . $ctx->exception->getMessage(),
            ['exception' => $ctx->exception],
        );
    }
}
