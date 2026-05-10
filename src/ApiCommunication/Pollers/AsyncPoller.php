<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Pollers;

use BAGArt\TelegramBot\ApiCommunication\Async\Scheduler\FiberScheduler;
use BAGArt\TelegramBot\ApiCommunication\TgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlMultiTransport;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Exceptions\TgApi\TgFloodWaitException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotOutputWrapper;
use Fiber;
use Throwable;

class AsyncPoller implements PollerContract
{
    public const string TYPE = 'async';

    public function __construct(
        private readonly UpdateDTOInitProcessor $updateProcessor,
        private readonly TgBotApiDTOClientContract $dtoClient,
        private readonly SchedulerContract $scheduler,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    public static function build(
        TgTypeDTOProcessorContract $updateProcessor,
        ?TgBotApiDTOClientContract $dtoClient = null,
        ?TgBotLogWrapper $logger = null,
        ?TgBotOutputWrapper $output = null,
    ): self {
        $logger ??= TgBotLogWrapper::build();
        $transport = new TgCurlMultiTransport(logger: $logger);

        return new self(
            updateProcessor: $updateProcessor,
            dtoClient: $dtoClient ?? TgBotApiDTOClient::build(
                transport: $transport,
                logger: $logger,
            ),
            scheduler: new FiberScheduler(
                transport: $transport,
                logger: $logger,
            ),
            logger: $logger,
        );
    }

    public function run(TgUpdateConfig $config): void
    {
        $this->logger?->debug('ASYNC POLLER START');

        $this->scheduler->enqueue(
            new \Fiber(function () use ($config): void {
                $offset = 0;
                while (true) {
                    try {
                        /**
                         * IMPORTANT:
                         * Next long polling request MUST NOT start
                         * until current batch processing is fully completed
                         * (unless hope mode enabled).
                         */
                        $response = $this->fetchUpdatesAsync($config, $offset);
                    } catch (Throwable $e) {
                        $this->handlePollingException($e);
                        continue;
                    }

                    $hasUpdates = false;

                    foreach ($response->result ?? [] as $update) {
                        $hasUpdates = true;

                        $offset = $update->updateId + 1;

                        $this->scheduler->enqueue(
                            new Fiber(function () use ($update, $config): void {
                                try {
                                    $this->handleUpdate($update, $config);
                                } catch (Throwable $e) {
                                    $this->logger?->error(
                                        'Update handling failed: '.$e->getMessage(),
                                        [
                                            'update_id' => $update->updateId,
                                        ]
                                    );

                                    $this->noticeUserAboutProcessingError(
                                        $update,
                                        $config,
                                        $e,
                                    );
                                }
                            })
                        );
                    }

                    /**
                     * ACK safety:
                     *
                     * Without "hope" mode enabled,
                     * we MUST fully drain scheduler here.
                     *
                     * This guarantees:
                     * - all update processors finished
                     * - all nested async tasks finished
                     * - no premature Telegram ACK
                     */
                    if (
                        $hasUpdates
                    ) {
                        $this->scheduler->drainUntilIdle();
                    } else {
                        /**
                         * We still tick scheduler
                         * so execution continues progressively.
                         */
                        $this->scheduler->tick();
                    }
                }
            })
        );

        while (true) {
            $this->scheduler->tick();
        }
    }

    private function handlePollingException(Throwable $e): void
    {
        $this->logger?->error(
            'Polling failed: '.$e->getMessage(),
        );

        if ($e instanceof TgFloodWaitException) {
            usleep(
                $e->getRetryAfter() * 1_000_000
            );

            return;
        }

        usleep(1_000_000);
    }

    private function noticeUserAboutProcessingError(
        UpdateTypeDTO $updateDTO,
        TgUpdateConfig $config,
        Throwable $e,
    ): void {
        $this->scheduler->enqueue(
            new Fiber(function () use (
                $updateDTO,
                $config,
            ): void {
                try {
                    $this->dtoClient->request(
                        token: $config->bot->token,
                        dto: new SendMessageMethodDTO(
                            chatId: $updateDTO->message->chat->id,
                            text: 'Processing message error',
                        ),
                    );
                } catch (Throwable $e) {
                    $this->logger?->error(
                        'noticeUserAboutProcessingError failed: '
                        .$e::class
                        .': '
                        .$e->getMessage(),
                        [
                            'update_id' => $updateDTO->updateId,
                        ]
                    );
                }
            })
        );
    }

    private function fetchUpdatesAsync(
        TgUpdateConfig $config,
        int $offset,
    ): TgApiResponse {
        $this->logger?->debug(
            'FETCH UPDATES START offset='.$offset
        );

        $dto = new GetUpdatesMethodDTO(
            offset: $offset,
            timeout: 30,
        );

        $promise = $this->dtoClient->requestAsync(
            $config->bot->token,
            $dto,
        );

        return $this->scheduler->await($promise);
    }

    public function handleUpdate(
        UpdateTypeDTO $update,
        TgUpdateConfig $config,
    ): void {
        $this->logger?->debug(
            'HANDLE UPDATE id='.$update->updateId
        );

        /**
         * Actual strict ordering logic
         * must be implemented inside processor layer,
         * not inside poller.
         */
        $this->updateProcessor->process(
            $update,
            'bot_id_here',
            $config,
            null,
            $this->scheduler,
        );
    }
}
