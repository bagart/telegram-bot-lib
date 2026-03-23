<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Pollers;

use BAGArt\TelegramBot\ApiCommunication\Async\Scheduler\FiberScheduler;
use BAGArt\TelegramBot\ApiCommunication\Async\Scheduler\PromiseFiberBridge;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Exceptions\TgApi\TgFloodWaitException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TypeDTOProcessor\DtoProcessorConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use Throwable;

class AsyncPoller implements PollerContract
{
    public const string TYPE = 'async';

    private readonly SchedulerContract $scheduler;

    public function __construct(
        private readonly TgBotApiDTOClientContract $dtoClient,
        private readonly UpdateDTOInitProcessor $updateProcessor,
        private readonly ?TgBotLogWrapper $logger = null,
        ?SchedulerContract $scheduler = null,
    ) {
        $this->scheduler = $scheduler ?? new FiberScheduler(
            logger: $logger,
        );
    }

    public function run(
        DtoProcessorConfig $config,
    ): void {
        $this->logger?->debug('ASYNC POLLER RUN START');
        $pollingFiber = new Fiber(function () use ($config) {
            $offset = 0;

            while (true) {
                try {
                    $response = $this->fetchUpdatesAsync(
                        $config->token,
                        $offset
                    );

                    if ($response->ok) {
                        $updates = $response->result;

                        if (is_array($updates) && count($updates) > 0) {
                            foreach ($updates as $update) {
                                // 2. For each update, spawn a new Fiber to handle it concurrently
                                if ($update instanceof UpdateTypeDTO) {
                                    $this->scheduler->enqueue(new Fiber(fn () => $this->handleUpdate($update, $config))
                                    );
                                }

                                if (
                                    $update instanceof UpdateTypeDTO
                                    && property_exists($update, 'update_id')
                                ) {
                                    $offset = $update->update_id + 1;
                                }
                            }
                        }
                    }
                } catch (Throwable $e) {
                    echo "[Poller Error] {$e->getMessage()}".PHP_EOL;
                    if ($e instanceof TgFloodWaitException) {
                        usleep($e->getRetryAfter() * 1_000_000);
                    } else {
                        usleep(1_000_000);
                    }
                }

                if (Fiber::getCurrent()) {
                    Fiber::suspend();
                }
            }
        });

        $this->scheduler->registerSuspendedFiber($pollingFiber);
        $this->scheduler->enqueue($pollingFiber);
        $this->logger?->debug('ASYNC POLLER ENQUEUED');
        $this->scheduler->run();
    }

    private function fetchUpdatesAsync(string $token, int $offset): TgApiResponse
    {
        $dto = new GetUpdatesMethodDTO(offset: $offset, timeout: 30);
        $promise = $this->dtoClient->requestAsync($token, $dto);

        return PromiseFiberBridge::await($promise);
    }

    public function handleUpdate(
        UpdateTypeDTO $update,
        DtoProcessorConfig $config,
    ): void {
        $updateId = null;
        if (is_object($update) && property_exists($update, 'update_id')) {
            $updateId = $update->update_id;
        }
        $this->logger?->debug('HANDLE UPDATE type='.get_class($update).' id='.($updateId ?? 'n/a'));
        $this->updateProcessor->process($update, 'bot_id_here', $config);
    }
}
