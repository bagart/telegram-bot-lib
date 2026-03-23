<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Pollers;

use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiReturnException;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\DtoProcessorConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgOutputWrapper;

class SyncPoller implements PollerContract
{
    public const string TYPE = 'sync';

    public function __construct(
        private readonly UpdateDTOInitProcessor $updateProcessor,
        private readonly TgBotLogWrapper $logger,
        private readonly ?TgBotCacheWrapper $cache = null,
        private readonly ?TgOutputWrapper $output = null,

    ) {
    }

    public function run(
        DtoProcessorConfig $config,
    ): void {
        $this->logger->info('SYNC POLLER RUN START');
        $lastId = 0;
        $dtoClient = TgBotApiDTOClient::build($this->cache, $this->logger);

        while (true) {
            try {
                $response = $dtoClient->request(
                    token: $config->token,
                    dto: new GetUpdatesMethodDTO(
                        offset: $config && $config->noAck ? 0 : $lastId,
                        limit: 100,
                        timeout: 60,
                        allowedUpdates: ['message', 'callback_query'],
                    ),
                );

                if ($response->ok) {
                    $updates = $response->result;

                    if (is_array($updates)) {
                        $processed = 0;
                        foreach ($updates as $update) {
                            if ($update instanceof UpdateTypeDTO) {
                                if ($update->updateId < $lastId) {
                                    continue;
                                }
                                $lastId = max($lastId, $update->updateId + 1);
                                $processed++;

                                $botId = explode(':', $config->token)[0];
                                $this->updateProcessor->process($update, $botId, $config);
                            }
                        }

                        if ($processed > 0 && $this->output !== null) {
                            $bar = $this->output->createProgressBar($processed);
                            $bar->start();
                            $bar->setProgress($processed);
                            $bar->finish();
                            $this->output->newLine();
                        }

                        if ($config && $config->noAck && $processed > 0) {
                            usleep(2_000_000);
                        }
                    }
                } else {
                    $this->logger->error(
                        'tg api getUpdates response not ok: '.json_encode($response, JSON_PRETTY_PRINT)
                    );
                }
            } catch (TgApiReturnException $e) {
                $this->logger->error(
                    'tg api getUpdates response '.$e::class.': '.$e->getMessage()
                );
            }
        }
    }
}
