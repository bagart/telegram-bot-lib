<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use Throwable;

/**
 * Critical rules:
 *
 * 1. Never create private scheduler here
 * 2. Must use shared scheduler from AsyncPoller
 * 3. No second event loop allowed
 * 4. No hidden ACK-breaking execution
 */
final class AsyncFiberDtoPipelineDispatcher implements DtoPipelineDispatcherContract
{
    public const string TYPE = 'async';

    public function __construct(
        private readonly SchedulerContract $scheduler,
        private readonly ?TgBotLogWrapper $logger = null,
    ) {
    }

    /**
     * @param  list<
     *     TgTypeDTOProcessorContract
     *     | class-string<TgTypeDTOProcessorContract>
     * > $processors
     */
    public function dispatch(
        TgUpdateConfig $config,
        TgApiTypeDTOContract $dto,
        string $botId,
        array $processors,
        ?string $action = null,
    ): int {
        if ($processors === []) {
            return 0;
        }

        $this->logger?->debug(
            sprintf(
                'ASYNC DISPATCH START processors=%d dto=%s action=%s',
                count($processors),
                $dto::class,
                $action ?? 'null',
            )
        );

        $enqueued = 0;

        foreach ($processors as $processor) {
            $instance = $this->resolveProcessor(
                processor: $processor,
                config: $config,
            );

            if ($instance === null) {
                continue;
            }

            /**
             * Critical: enqueue only.
             * Never run/tick/wait here.
             * Shared scheduler only.
             */
            $this->scheduler->enqueue(
                new Fiber(function () use (
                    $instance,
                    $dto,
                    $botId,
                    $config,
                    $action
                ): void {
                    $this->safeProcess(
                        processor: $instance,
                        dto: $dto,
                        botId: $botId,
                        config: $config,
                        action: $action,
                    );
                })
            );

            ++$enqueued;
        }
        $this->logger?->debug(
            sprintf(
                'ASYNC DISPATCH ENQUEUED=%d dto=%s action=%s',
                $enqueued,
                $dto::class,
                $action ?? 'null',
            )
        );

        return $enqueued;
    }

    private function safeProcess(
        TgTypeDTOProcessorContract $processor,
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action,
    ): void {
        try {
            $this->logger?->debug(
                sprintf(
                    'PROCESSOR START: %s',
                    $processor::class,
                )
            );

            /**
             * Critical:
             * same shared scheduler must be passed further.
             */
            $processor->process(
                dto: $dto,
                botId: $botId,
                config: $config,
                action: $action,
                scheduler: $this->scheduler,
            );

            $this->logger?->debug(
                sprintf(
                    'PROCESSOR DONE: %s',
                    $processor::class,
                )
            );
        } catch (Throwable $e) {
            // Critical failures only.
            $this->logger?->error(
                sprintf(
                    'PROCESSOR FAILED: %s :: %s',
                    $processor::class,
                    $e->getMessage(),
                ),
                [
                    'processor' => $processor::class,
                    'dto' => $dto::class,
                    'action' => $action,
                    'exception' => $e::class,
                ]
            );
        }
    }

    private function resolveProcessor(
        TgTypeDTOProcessorContract|string $processor,
        TgUpdateConfig $config,
    ): ?TgTypeDTOProcessorContract {
        try {
            if (
                $processor
                instanceof TgTypeDTOProcessorContract
            ) {
                return $processor;
            }

            if (!class_exists($processor)) {
                $this->logger?->error(
                    sprintf(
                        'PROCESSOR CLASS NOT FOUND: %s',
                        $processor,
                    )
                );

                return null;
            }

            if (
                is_subclass_of(
                    $processor,
                    TgTypeDTOProcessorContract::class,
                )
            ) {

                /**
                 * Important:
                 * processor build stays sync,
                 * execution stays async.
                 */
                /** @var TgTypeDTOProcessorContract $instance */
                $instance = $processor::build(
                    config: $config,
                    logger: $this->logger,
                );

                return $instance;
            }

            $this->logger?->error(
                sprintf(
                    'INVALID PROCESSOR CONTRACT: %s',
                    $processor,
                )
            );

            return null;
        } catch (Throwable $e) {
            $this->logger?->error(
                sprintf(
                    'PROCESSOR RESOLVE FAILED: %s :: %s',
                    is_string($processor)
                        ? $processor
                        : $processor::class,
                    $e->getMessage(),
                ),
                [
                    'exception' => $e::class,
                ]
            );

            return null;
        }
    }
}
