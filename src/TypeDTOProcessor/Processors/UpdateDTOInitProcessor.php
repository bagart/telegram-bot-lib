<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors;

use BAGArt\TelegramBot\ApiCommunication\Async\PipelineDispatcherRegistry;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\StrictUpdateExecutionCoordinator;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use Fiber;
use Throwable;

/**
 * Critical responsibilities:
 *
 * 1. Global async processing entrypoint
 * 2. Strict ordered processor execution orchestration
 * 3. Shared scheduler propagation
 * 4. Per (chat_id + ProcessorClassName) execution ordering
 *
 * Poller must not contain this logic.
 */
class UpdateDTOInitProcessor implements TgTypeDTOProcessorContract
{
    /**
     * @var array<string, array<TgTypeDTOProcessorContract>>
     */
    private array $cachedProcessors = [];

    private readonly TgBotLogWrapper $logger;
    private readonly PipelineDispatcherRegistry $dispatcherRegistry;
    private readonly StrictUpdateExecutionCoordinator $strictCoordinator;

    public static function build(
        TgUpdateConfig $config,
        ?TgBotLogWrapper $logger = null,
    ): self {
        return new static(
            processorRegistry: TypeDTOProcessorRegistry::build(),
            logger: $logger,
        );
    }

    public function __construct(
        private readonly TypeDTOProcessorRegistry $processorRegistry,
        ?PipelineDispatcherRegistry $dispatcherRegistry = null,
        ?TgBotLogWrapper $logger = null,
    ) {
        $this->dispatcherRegistry = $dispatcherRegistry ?? PipelineDispatcherRegistry::build();

        $this->logger = $logger ?? TgBotLogWrapper::build();

        $this->strictCoordinator = new StrictUpdateExecutionCoordinator(
            logger: $this->logger,
        );
    }

    public function process(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action = null,
        ?SchedulerContract $scheduler = null,
    ): void {
        foreach ($dto::tgPropertyMetas() as $meta) {
            if (!property_exists($dto, $meta->property)) {
                $this->logger->warning(
                    'Unexpected DTO Property from meta: '
                    .$dto::class
                    ."::{$meta->property}"
                );

                continue;
            }

            $value = $dto->{$meta->property};

            if (!$value instanceof TgApiTypeDTOContract) {
                continue;
            }

            $this->processDto(
                dto: $value,
                botId: $botId,
                config: $config,
                scheduler: $scheduler,
                action: $meta->tgPropName,
                update: $dto instanceof UpdateTypeDTO ? $dto : null,
            );
        }
    }

    private function processDto(
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?SchedulerContract $scheduler = null,
        ?string $action = null,
        ?UpdateTypeDTO $update = null,
    ): void {
        $supportingProcessors = $this->resolveSupportingProcessors(
            dto: $dto,
            config: $config,
            action: $action,
        );

        if ($supportingProcessors === []) {
            return;
        }

        /**
         * Existing dispatcher compatibility preserved.
         */
        if ($config->dispatcher !== null) {
            $this->dispatcherRegistry
                ->make(
                    type: $config->dispatcher,
                    scheduler: $scheduler
                )
                ->dispatch(
                    config: $config,
                    dto: $dto,
                    botId: $botId,
                    processors: $supportingProcessors,
                    action: $action,
                );

            return;
        }

        $strictProcessorClassNames = [];

        if ($update !== null) {
            $strictProcessorClassNames = $this->getStrictOrderedProcessorClassNames($update, $config);
        }

        foreach ($supportingProcessors as $processor) {
            $processorClass = $processor::class;

            if (
                $update !== null
                && in_array(
                    $processorClass,
                    $strictProcessorClassNames,
                    true
                )
            ) {
                $this->enqueueStrictOrderedProcessor(
                    processor: $processor,
                    processorClass: $processorClass,
                    dto: $dto,
                    update: $update,
                    botId: $botId,
                    config: $config,
                    scheduler: $scheduler,
                    action: $action,
                );

                continue;
            }

            /**
             * Normal async processor
             */
            $scheduler->enqueue(
                new Fiber(fn () => $this->safeProcess(
                    processor: $processor,
                    dto: $dto,
                    botId: $botId,
                    config: $config,
                    action: $action,
                ))
            );
        }
    }

    /**
     * Override in real project if needed.
     */
    public function getStrictOrderedProcessorClassNames(
        UpdateTypeDTO $update,
        TgUpdateConfig $config,
    ): array {
        return [];
    }

    private function enqueueStrictOrderedProcessor(
        TgTypeDTOProcessorContract $processor,
        string $processorClass,
        TgApiTypeDTOContract $dto,
        UpdateTypeDTO $update,
        string $botId,
        TgUpdateConfig $config,
        SchedulerContract $scheduler,
        ?string $action,
    ): void {
        $chatId = $this->extractChatId($update);

        if ($chatId === null) {
            $scheduler->enqueue(
                new Fiber(fn () => $this->safeProcess(
                    processor: $processor,
                    dto: $dto,
                    botId: $botId,
                    config: $config,
                    action: $action,
                ))
            );

            return;
        }

        $this->strictCoordinator->enqueue(
            processorClass: $processorClass,
            chatId: $chatId,
            scheduler: $scheduler,
            task: fn () => $this->safeProcess(
                processor: $processor,
                dto: $dto,
                botId: $botId,
                config: $config,
                action: $action,
            )
        );
    }

    private function safeProcess(
        TgTypeDTOProcessorContract $processor,
        TgApiTypeDTOContract $dto,
        string $botId,
        TgUpdateConfig $config,
        ?string $action,
    ): void {
        try {
            $processor->process(
                dto: $dto,
                botId: $botId,
                config: $config,
                action: $action,
            );
        } catch (Throwable $e) {
            // Critical only.
            $this->logger->error(
                'Processor execution failed',
                [
                    'processor' => $processor::class,
                    'dto' => $dto::class,
                    'action' => $action,
                    'exception' => [
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ],
                ]
            );
        }
    }

    /**
     * @return array<TgTypeDTOProcessorContract>
     */
    private function resolveSupportingProcessors(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action,
    ): array {
        $cacheKey = $dto::class;

        if (isset($this->cachedProcessors[$cacheKey])) {
            return array_values(
                array_filter(
                    $this->cachedProcessors[$cacheKey],
                    fn (TgTypeDTOProcessorContract $processor): bool => $processor
                        ->support(
                            $dto,
                            $config,
                            $action,
                        )
                )
            );
        }

        $resolved = [];

        foreach (
            $this->processorRegistry->get(
                $dto::class,
                $config,
            ) as $processor
        ) {
            if ($processor->support(
                dto: $dto,
                config: $config,
                action: $action,
            )) {
                $resolved[] = $processor;
            }
        }

        $this->cachedProcessors[$cacheKey] = $resolved;

        return $resolved;
    }

    private function extractChatId(
        UpdateTypeDTO $update,
    ): int|string|null {
        return $update->message?->chat?->id
            ?? $update->callbackQuery?->message?->chat?->id
            ?? $update->editedMessage?->chat?->id;
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgUpdateConfig $config,
        ?string $action = null,
    ): bool {
        return $dto instanceof UpdateTypeDTO;
    }
}
