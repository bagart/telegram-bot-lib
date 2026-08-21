<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Redis;

use BAGArt\ASKClient\Contracts\Queue\JobExecutorContract;
use BAGArt\AsyncKernel\Job\AsyncJob;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgPartitionSchedulerContract;
use BAGArt\TelegramBot\Contracts\Processing\Redis\TgProcessorFactoryContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;

final class TgJobExecutor implements JobExecutorContract
{
    public function __construct(
        private readonly TgProcessorFactoryContract $processorFactory,
        private readonly TgServiceConfig $serviceConfig,
        private readonly ?TgPartitionSchedulerContract $partitionScheduler = null,
        private readonly ?BotProcessorContext $processorContext = null,
    ) {
    }

    public function execute(AsyncJob $job): void
    {
        /** @var TgAsyncJob $job */
        $processor = $this->processorFactory->create(
            $job->context->processor,
            $this->processorContext
            ?? throw new \LogicException('TgJobExecutor requires a BotProcessorContext to build processors'),
        );

        if ($this->partitionScheduler !== null) {
            $processor->setPartitionScheduler($this->partitionScheduler);
        }

        $processor->process(
            dto: $job->context->dto,
            botConfig: $job->context->botConfig,
        );
    }
}
