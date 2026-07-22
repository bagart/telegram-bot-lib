<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

use BAGArt\ASKClient\Dns\AskDnsRegistry;
use BAGArt\ASKClient\Queue\Adapters\InMemoryQueueAdapter;
use BAGArt\ASKClient\Transporting\HttpTransports\ASKSocketTransport;
use BAGArt\AsyncKernel\Cache\InMemoryCache;
use BAGArt\AsyncKernel\Config\PartitionConfig;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgAdvancedRateLimiter;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\AsyncFiberProcessingDispatcher;

class TgServiceConfig
{
    public function __construct(
        public string $dispatcher = AsyncFiberProcessingDispatcher::TYPE,
        public string $transport = ASKSocketTransport::TYPE,
        public string $cacheDriver = InMemoryCache::TYPE,
        public ?string $rateLimiter = TgAdvancedRateLimiter::NAME,
        public string $logLevel = ASKLogWrapper::LEVEL_DEFAULT,
        public string $processingEngine = InMemoryQueueAdapter::TYPE,
        public ?PartitionConfig $partitionConfig = null,
        public DaemonRuntime $daemonRuntime = new DaemonRuntime(),
        public ?OutboundWorkerConfig $outboundWorkerConfig = null,
        public string $outboundQueueStore = InMemoryOutboundQueue::TYPE,
        public ?string $redisDsn = null,
        public string $dns = AskDnsRegistry::DEFAULT_ADAPTER,
    ) {
    }
}
