<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing;

use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;

interface ProcessingErrorActionContract
{
    public function execute(ProcessorErrorContext $ctx): void;

    public function supports(\Throwable $e): bool;
}
