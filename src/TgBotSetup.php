<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\ASKClient\Contracts\Client\ApiClientContract;
use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\Contracts\ASKLockerContract;
use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\ApiCommunication\Polling\ProcessingStatistics;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\Outbound\TgSenderContract;
use BAGArt\TelegramBot\Contracts\Processing\TgDbLoggerContract;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;

final class TgBotSetup
{
    public function __construct(
        public readonly ASKLogWrapper $logger,
        public readonly ASKCacheWrapper $cache,
        public readonly ASKQueueAdapterContract $queue,
        public readonly ASKLockerContract $locker,
        public readonly TgBotApiTransportContract $transport,
        public readonly TgBotApiDTOClientContract $dtoClient,
        public readonly TgApiCaller $tgApiCaller,
        public readonly TypeDTOProcessorRegistry $processorRegistry,
        public readonly ProcessingStatistics $processingStatistics,
        public readonly ApiClientContract $apiClient,
        public readonly ASKSchedulerContract $processorScheduler,
        public readonly TgSenderContract $tgSender,
        public readonly TgOutboundStats $outboundStats,
        public TgServiceConfig $serviceConfig,
        public ?TgDbLoggerContract $dbLogger = null,
    ) {
    }
}
