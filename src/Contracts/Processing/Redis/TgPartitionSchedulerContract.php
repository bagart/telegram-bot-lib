<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Redis;

use BAGArt\TelegramBot\Processing\Redis\TgAsyncJob;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

interface TgPartitionSchedulerContract
{
    public function enqueue(UpdateContext $context): ?TgAsyncJob;

    public function enqueueJob(TgAsyncJob $job): void;
}
