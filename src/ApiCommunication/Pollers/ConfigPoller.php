<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Pollers;

use BAGArt\TelegramBot\ApiCommunication\PollerRegistry;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlMultiTransport;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotOutputWrapper;

class ConfigPoller implements PollerContract
{
    public const string TYPE = 'config';

    public function __construct(
        private readonly UpdateDTOInitProcessor $updateProcessor,
        private readonly ?PollerRegistry $pollerRegistry = null,
        private readonly ?TgBotApiDTOClientContract $dtoClient = null,
        private readonly ?TgBotCacheWrapper $cache = null,
        private readonly ?TgBotLogWrapper $logger = null,
        private readonly ?TgBotOutputWrapper $output = null,
    ) {
    }

    public static function build(
        TgTypeDTOProcessorContract $updateProcessor,
        ?TgBotApiDTOClientContract $dtoClient = null,
        ?TgBotLogWrapper $logger = null,
        ?TgBotOutputWrapper $output = null,
    ): self {
        return new self(
            updateProcessor: $updateProcessor,
            dtoClient: $dtoClient,
            logger: $logger,
            output: $output,
        );
    }

    public function run(TgUpdateConfig $config): void
    {
        ($this->pollerRegistry ?? PollerRegistry::build())
            ->make(
                type: $config->poller,
                updateProcessor: $this->updateProcessor,
                dtoClient: $this->dtoClient ?? TgBotApiDTOClient::build(
                    cache: $this->cache ?? TgBotCacheWrapper::build(),
                    transport: $config->poller === AsyncPoller::TYPE
                        ? new TgCurlMultiTransport(
                            logger: $this->logger,
                        )
                        : null,
                    logger: $this->logger,
                ),
                output: $this->output,
            )
            ->run($config);
    }
}
