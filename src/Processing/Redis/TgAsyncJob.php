<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\AsyncKernel\Job\AsyncJob;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;

final class TgAsyncJob extends AsyncJob
{
    /**
     * @param class-string<\BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract> $processor
     */
    public function __construct(
        string $jobId,
        ?string $partitionKey,
        public readonly UpdateContext $context,
        int $createdAt,
        int $attempt = 0,
        ?int $retryAt = null,
    ) {
        parent::__construct(
            jobId: $jobId,
            partitionKey: $partitionKey,
            processor: $context->processor,
            executionKey: $context->executionKey,
            createdAt: $createdAt,
            attempt: $attempt,
            retryAt: $retryAt,
        );
    }
}
