<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Update;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\AsyncKernel\Exceptions\ASKInterruptException;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\UpdateRouterContract;
use BAGArt\TelegramBot\Processing\BotProcessorContext;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorConsumer;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\Processing\Execution\OrderedExecutionCoordinator;
use BAGArt\TelegramBot\TgBotSetup;
use Throwable;

final class UpdateRouter implements UpdateRouterContract
{
    public function __construct(
        private readonly TgServiceConfig $serviceConfig,
        private readonly TgBotSetup $botSetup,
        private readonly ProcessingErrorConsumer $errorConsumer,
        private readonly ASKSchedulerContract $scheduler = new ASKFiberScheduler,
        private readonly ASKSchedulerContract $processorsScheduler = new ASKFiberScheduler,
        private readonly OrderedExecutionCoordinator $coordinator = new OrderedExecutionCoordinator,
    ) {}

    public function dispatch(UpdateContext $updateContext): void
    {
        // @todo queue by $serviceConfig
        // $this->partitionScheduler->enqueue($updateContext);
        $task = new ProcessUpdateTask($this, $updateContext);

        if ($updateContext->executionKey !== null) {
            $runnable = $this->coordinator->enqueue(
                key: $updateContext->executionKey,
                task: $task,
            );

            if ($runnable === null) {
                return;
            }

            $this->scheduler->enqueue($runnable);

            return;
        }

        // Scheduler API is Fiber|Closure — first-class callable of the named task.
        $this->scheduler->enqueue($task->execute(...));
    }

    /**
     * Internal entry the {@see ProcessUpdateTask} calls — kept beside dispatch()
     * so ordered tasks stay named objects without exposing processing to callers.
     */
    public function runProcess(UpdateContext $updateContext): void
    {
        $this->process($updateContext);
    }

    private function process(UpdateContext $updateContext): void
    {
        $processor = $updateContext->processor::build(
            context: BotProcessorContext::fromBotSetup($this->botSetup),
        );

        try {
            $processor->process(
                dto: $updateContext->dto,
                botConfig: $updateContext->botConfig,
            );
        } catch (ASKInterruptException $e) {
            throw $e;
        } catch (Throwable $e) {
            $processorErrorContext = new ProcessorErrorContext(
                exception: $e,
                processor: $processor,
                dto: $updateContext->dto,
                botConfig: $updateContext->botConfig,
            );

            $processor->onException($processorErrorContext);
            $this->errorConsumer->handle($processorErrorContext);
        }
    }

    public function tickable(): array
    {
        return [
            $this->scheduler,
            $this->processorsScheduler,
        ];
    }
}
