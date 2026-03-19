<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions;

use BAGArt\TelegramBot\Contracts\Processing\ProcessingErrorActionContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\Processing\Services\ProcessingErrorUserNotifier;

final class NotifyUserErrorAction implements ProcessingErrorActionContract
{
    public function __construct(
        private readonly ProcessingErrorUserNotifier $notifier,
    ) {
    }

    public function supports(\Throwable $e): bool
    {
        return true;
    }

    public function execute(ProcessorErrorContext $ctx): void
    {
        $this->notifier->notify($ctx->dto, $ctx->botConfig);
    }
}
