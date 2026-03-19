<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\TgUpdateProcessorSelectorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessorErrorContext;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetup;

class UpdateDTOInitProcessor implements TgTypeDTOProcessorContract
{
    public function __construct(
        private readonly TgServiceConfig $serviceConfig,
        private readonly TgUpdateProcessorSelectorContract $processorSelector,
        private readonly ?ProcessingDispatcherContract $dispatcher = null,
    ) {
    }

    public static function build(
        TgServiceConfig $serviceConfig,
        TgBotSetup $botSetup,
        ?ProcessingDispatcherContract $dispatcher = null,
    ): self {
        return new self(
            serviceConfig: $serviceConfig,
            processorSelector: new RegisteredUpdateProcessorSelector(
                serviceConfig: $serviceConfig,
                botSetup: $botSetup,
            ),
            dispatcher: $dispatcher,
        );
    }

    public function support(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return $dto instanceof UpdateTypeDTO;
    }

    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }

    public function process(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action = null,
        ?TgApiTypeDTOContract $updateDto = null,
    ): void {
        assert($dto instanceof UpdateTypeDTO);

        $selectedProcessors = $this->processorSelector->selectProcessors(
            updateDTO: $dto,
            botConfig: $botConfig,
        );

        foreach ($selectedProcessors as $property => $processorList) {
            foreach ($processorList as $processor) {
                assert($processor instanceof TgTypeDTOProcessorContract);

                $subDto = $dto->{$property};

                $processor->executionKey($subDto);

                if ($this->dispatcher !== null) {
                    $this->dispatcher->dispatch(
                        serviceConfig: $this->serviceConfig,
                        botConfig: $botConfig,
                        dto: $subDto,
                        processors: [$processor],
                        action: $property,
                        updateDto: $processor->isNeedUpdateDTO() ? $dto : null,
                    );
                } else {
                    $processor->process(
                        dto: $subDto,
                        botConfig: $botConfig,
                        action: $property,
                        updateDto: $processor->isNeedUpdateDTO() ? $dto : null,
                    );
                }
            }
        }
    }

    public function onException(
        ProcessorErrorContext $context,
    ): void {
    }
}
