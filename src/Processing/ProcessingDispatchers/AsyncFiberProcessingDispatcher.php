<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ProcessingDispatchers;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use Fiber;
use Throwable;

/**
 * Critical rules:
 *
 * 1. Never create private scheduler here
 * 2. Must use shared scheduler from AsyncTgPoller
 * 3. No second event loop allowed
 * 4. No hidden ACK-breaking execution
 */
final class AsyncFiberProcessingDispatcher implements ProcessingDispatcherContract
{
    public const string TYPE = 'async';

    public function __construct(
        private readonly ASKSchedulerContract $scheduler,
        private readonly ?ASKLogWrapper $logger = null,
        private readonly ?BotProcessorContext $processorContext = null,
    ) {
    }

    public function dispatch(
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        TgApiTypeDTOContract $dto,
        array $processors,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
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
                serviceConfig: $serviceConfig,
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
                    $serviceConfig,
                    $botConfig,
                    $action,
                    $updateDto,
                ): void {
                    $this->safeProcess(
                        processor: $instance,
                        dto: $dto,
                        serviceConfig: $serviceConfig,
                        botConfig: $botConfig,
                        action: $action,
                        updateDto: $updateDto,
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
        TgServiceConfig $serviceConfig,
        TgBotConfig $botConfig,
        ?string $action,
        ?TgApiTypeDTOContract $updateDto = null,
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
                botConfig: $botConfig,
                action: $action,
                updateDto: $updateDto,
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
        TgServiceConfig $serviceConfig,
    ): ?TgTypeDTOProcessorContract {
        try {
            if ($processor instanceof TgTypeDTOProcessorContract) {
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
                    context: $this->processorContext,
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
