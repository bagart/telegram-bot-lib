<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Redis;

use BAGArt\TelegramBot\Processing\Redis\TgAsyncJob;
use Throwable;

interface TgProcessingErrorLoggerContract
{
    public function log(TgAsyncJob $job, Throwable $e, int $attempt): void;
}
