<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Pollers;

use BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry;
use BAGArt\TelegramBot\ApiCommunication\PollerRegistry;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\TypeDTOProcessor\DtoProcessorConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

class ConfigPoller implements PollerContract
{
    public const string TYPE = 'config';

    public function __construct(
        private readonly TypeDTOProcessorRegistry $processorRegistry,
        private readonly ?PipelineDispatcherRegistry $dispatcherRegistry = null,
        private readonly ?PollerRegistry $pollerRegistry = null,
        private readonly ?TgBotCacheWrapper $cache = null,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    public function run(
        DtoProcessorConfig $config,
    ): void {
        ($this->pollerRegistry ?? PollerRegistry::build())
            ->make(
                type: $config->poller,
                updateProcessor: new UpdateDTOInitProcessor(
                    processorRegistry: $this->processorRegistry,
                    dispatcherRegistry: $this->dispatcherRegistry,
                    logger: $this->logger,
                ),
                dtoClient: TgBotApiDTOClient::build(
                    cache: $this->cache,
                    logger: $this->logger,
                ),
            )
            ->run($config);
    }
}
