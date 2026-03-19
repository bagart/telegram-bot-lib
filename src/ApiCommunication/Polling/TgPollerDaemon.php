<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Polling;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\ASKShutdownContext;
use BAGArt\AsyncKernel\Contracts\ASKBackpressureStrategyContract;
use BAGArt\AsyncKernel\Contracts\ASKProducerContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKDaemonContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\AsyncKernel\Exceptions\ASKInterruptException;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgPollerConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\TgUpdateProcessorSelectorContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiNetworkException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;
use BAGArt\TelegramBot\Exceptions\TgApi\TgApiException;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Processing\Update\UpdateContext;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use Throwable;

final readonly class TgPollerDaemon implements
    ASKDaemonContract,
    ASKProducerContract,
    WithASKTickableContract
{
    public function __construct(
        private TgBotConfig $botConfig,
        private ASKQueueAdapterContract $queue,
        private TgBotApiDTOClientContract $dtoClient,
        private TgUpdateProcessorSelectorContract $updateProcessorSelector,
        private ASKLogWrapper $logger,
        private TgPollerConfig $pollerConfig = new TgPollerConfig(),
        private PollerState $pollerState = new PollerState(),
        private ProcessingStatistics $processingStatistics = new ProcessingStatistics(),
        private ?int $maxQueueSize = null,
        private string $queueName = 'tg-inbox',
        private string $name = 'TgPollerDaemon',
        private ?ASKBackpressureStrategyContract $backpressureStrategy = null,
    ) {
    }

    public function produce(int $systemPressure): void
    {
        $this->logger->debug(
            "[TgPoller::produce] new poll offset={$this->pollerState->offset()}"
        );

        $this->pollerState->pollerIsPromised = true;
        try {
            $response = $this->dtoClient
                ->requestAsync(
                    botConfig: $this->botConfig,
                    dto: $this->getUpdatesMethodDTO(),
                    timeout: $this->pollerConfig->timeout,
                )
                ->await();
        } catch (TgApiRateLimitException $e) {
            $this->pollerState->nextPollAfter = time() + ($e->getRetryAfter() ?: 2);

            return;
        } catch (TgApiNetworkException|TgApiException $e) {
            $this->logger->error(
                '[TgPoller::produce] '.$e::class.": {$e->getMessage()}"
            );
            $this->pollerState->nextPollAfter = time() + 2;

            return;
        } finally {
            $this->pollerState->pollerIsPromised = false;
        }

        $updates = $this->getUpdatesFromResponse($response);
        $this->logger->debug(
            "[TgPollerDaemon::produce] ".count($updates).' updates polled'
        );

        foreach ($updates as $updateDTO) {
            $contextList = $this->getUpdateContextFromUpdate($updateDTO);
            $this->pollerState->advance($updateDTO->updateId);

            foreach ($contextList as $context) {
                $this->queue->push($this->queueName, serialize($context));
            }
        }
    }

    private function getUpdatesFromResponse(TgApiResponse $response): array
    {
        if (!$this->pollerConfig->noAck) {
            $this->pollerState->lastAckedUpdatedId = $this->pollerState->lastUpdateId;
        }

        $updates = [];

        foreach ($response->result ?? [] as $updateDto) {
            if (!$updateDto instanceof UpdateTypeDTO) {
                continue;
            }

            if ($updateDto->updateId <= $this->pollerState->lastUpdateId) {
                continue;
            }

            $updates[$updateDto->updateId] = $updateDto;
        }

        return $updates;
    }

    private function getUpdateContextFromUpdate(UpdateTypeDTO $updateDTO): array
    {
        $this->processingStatistics->updateScheduled++;

        $processors = $this->updateProcessorSelector->selectProcessors(
            updateDTO: $updateDTO,
            botConfig: $this->botConfig,
        );
        $result = [];
        foreach ($processors as $property => $processorList) {
            foreach ($processorList as $processor) {
                assert($processor instanceof TgTypeDTOProcessorContract);
                $dto = $updateDTO->$property;
                try {
                    $executionKey = $processor->executionKey($dto);
                } catch (ASKInterruptException $e) {
                    throw $e;
                } catch (Throwable $e) {
                    $this->logger->critical(
                        '[IGNORE] [TgPollerDaemon] Technical error with processor: '
                        .'Unable to generate processor executionKey ('.$processor::class.'); '
                        .$e::class.": {$e->getMessage()}",
                        ['dto' => $dto, 'exception' => $e],
                    );
                    throw new ASKInterruptException(
                        source: self::class,
                        message: 'Broken '.$processor::class.'::executionKey',
                    );
                }

                $result[] = new UpdateContext(
                    dto: $dto,
                    processor: $processor::class,
                    botConfig: $this->botConfig,
                    executionKey: $executionKey,
                    jobId: bin2hex(random_bytes(16)),
                    source: null,
                );
            }
        }

        return $result;
    }

    public function shutdown(ASKShutdownContext $context): bool
    {
        if (!$this->pollerState->shutdown) {
            $this->logger->info('Tg Poller shutdown initiated');
            $this->pollerState->shutdown = true;
            $this->pollerState->ackInProgress = false;
            $this->pollerState->ackRetries = 0;
        }

        if ($this->pollerConfig->noAck) {
            return true;
        }
        if ($this->queue->size($this->queueName) !== 0) {
            return false;
        }

        if ($this->pollerState->lastAckedUpdatedId >= $this->pollerState->lastUpdateId) {
            return true;
        }

        if ($this->pollerState->ackRetries >= 3) {
            $this->logger->warning('[TgPoller::shutdown] ACK retry limit reached — forcing complete');
            $this->pollerState->lastAckedUpdatedId = $this->pollerState->lastUpdateId;

            return true;
        }

        if ($this->pollerState->ackInProgress) {
            return false;
        }

        $this->pollerState->ackInProgress = true;

        try {
            $this->logger->debug('[TgPoller::shutdown] final ack');

            $this->dtoClient
                ->requestAsync(
                    botConfig: $this->botConfig,
                    dto: $this->getUpdatesMethodDTO(
                        limit: 1,
                        timeout: 0,
                    ),
                )
                ->await();

            $this->pollerState->lastAckedUpdatedId = $this->pollerState->lastUpdateId;
            $this->pollerState->ackInProgress = false;

            $this->logger->debug('[TgPoller::shutdown] ack completed');
        } catch (TgApiNetworkException $e) {
            $this->pollerState->ackInProgress = false;
            $this->pollerState->ackRetries++;
            $this->logger->warning(
                '[TgPoller::shutdown] ack failed: '.$e::class.": {$e->getMessage()} (retry {$this->pollerState->ackRetries}/3)"
            );

            return false;
        } catch (Throwable $e) {
            $this->pollerState->ackInProgress = false;
            $this->logger->warning(
                "[IGNORE] [TgPoller::shutdown] ack failed: {$e->getMessage()}"
            );
        }

        return true;
    }

    public function canProduce(): bool
    {
        if ($this->pollerState->shutdown) {
            return false;
        }

        if ($this->pollerState->pollerIsPromised) {
            return false;
        }

        if (time() < $this->pollerState->nextPollAfter) {
            return false;
        }

        if (
            $this->maxQueueSize !== null
            && $this->queue->size($this->queueName) >= $this->maxQueueSize
        ) {
            return false;
        }

        if (
            $this->backpressureStrategy !== null
            && !$this->backpressureStrategy->backpressure(
                systemPressure: $this->pressure(),
                currentPressure: $this->pressure(),
            )
        ) {
            return false;
        }

        return $this->queue->size($this->queueName)
            <= $this->pollerConfig->allowedMaxInboxSizeToPoll;
    }

    public function pressure(): int
    {
        return (int)round(100 * $this->queue->size($this->queueName) / ($this->maxQueueSize ?? 256));
    }

    private function getUpdatesMethodDTO(
        ?int $offset = null,
        ?int $limit = null,
        ?int $timeout = null,
    ): GetUpdatesMethodDTO {
        return new GetUpdatesMethodDTO(
            offset: $offset ?? ($this->pollerConfig->noAck ? 0 : $this->pollerState->offset()),
            limit: $limit ?? $this->pollerConfig->limit,
            timeout: $timeout ?? $this->pollerConfig->timeout,
            allowedUpdates: $this->pollerConfig->allowedUpdates,
        );
    }

    public function onError(Throwable $e): void
    {
        throw $e;
    }

    public function startup(): void
    {
        $this->pollerState->runed = true;

        $this->logger->info(
            'Tg Poller started [ASYNC]'
            .($this->pollerConfig->allowedMaxInboxSizeToPoll ? ' [TURBO]' : '')
            .($this->pollerConfig->noAck ? ' [NO-ACK]' : '')
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tickable(): array
    {
        return $this->dtoClient->tickable();
    }
}
