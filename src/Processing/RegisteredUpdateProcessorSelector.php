<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\TgUpdateProcessorSelectorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetup;
use Generator;

class RegisteredUpdateProcessorSelector implements TgUpdateProcessorSelectorContract
{
    private array $cachedProcessors = [];

    public function __construct(
        private readonly TgServiceConfig $serviceConfig,
        private readonly TgBotSetup $botSetup,
    ) {
    }

    public function selectProcessors(
        UpdateTypeDTO $updateDTO,
        TgBotConfig $botConfig,
    ): Generator {
        foreach ($updateDTO::tgPropertyMetas() as $meta) {
            if (!property_exists($updateDTO, $meta->property)) {
                continue;
            }

            $value = $updateDTO->{$meta->property};

            if (!$value instanceof TgApiTypeDTOContract) {
                continue;
            }

            $processors = $this->resolveSupportingProcessors(
                dto: $value,
                botConfig: $botConfig,
                action: $meta->tgPropName,
            );

            if ($processors !== []) {
                yield $meta->property => $processors;
            }
        }
    }

    private function resolveSupportingProcessors(
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action,
    ): array {
        $cacheKey = $dto::class;

        if (isset($this->cachedProcessors[$cacheKey])) {
            return array_values(
                array_filter(
                    $this->cachedProcessors[$cacheKey],
                    fn (TgTypeDTOProcessorContract $processor): bool => $processor
                        ->support($dto, $botConfig, $action)
                )
            );
        }

        $resolved = [];
        $processors = $this->botSetup->processorRegistry
            ->get(
                dto: $dto::class,
                context: BotProcessorContext::fromBotSetup($botConfig),
            );

        foreach ($processors as $processor) {
            if ($processor->support(
                dto: $dto,
                botConfig: $botConfig,
                action: $action
            )) {
                $resolved[] = $processor;
            }
        }

        $this->cachedProcessors[$cacheKey] = $resolved;

        return $resolved;
    }
}
