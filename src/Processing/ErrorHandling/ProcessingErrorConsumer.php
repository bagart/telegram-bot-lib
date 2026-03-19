<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ErrorHandling;

final class ProcessingErrorConsumer
{
    public function __construct(
        private readonly ProcessingErrorRegistry $registry,
    ) {
    }

    /**
     * AKKA: input = exception context.
     * Dispatcher calls each registered action.
     */
    public function handle(ProcessorErrorContext $ctx): void
    {
        foreach ($this->registry->resolve($ctx->exception) as $action) {
            $action->execute($ctx);
        }
    }
}
