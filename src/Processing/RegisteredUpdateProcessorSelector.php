<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Modules\ModuleEnablementContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgModuleProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\Processing\TgUpdateProcessorSelectorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Modules\TgCommandRegistry;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetup;
use Generator;

class RegisteredUpdateProcessorSelector implements TgUpdateProcessorSelectorContract
{
    private array $cachedProcessors = [];

    public function __construct(
        private readonly TgServiceConfig $serviceConfig,
        private readonly TgBotSetup $botSetup,
        private readonly ?ModuleEnablementContract $moduleEnablement = null,
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
        $chatId = $this->chatIdOf($dto);
        $commandName = $this->commandNameOf($dto);
        $cacheKey = $dto::class.'|'.$botConfig->botId.'|'.$chatId.'|'.$commandName;

        if (isset($this->cachedProcessors[$cacheKey])) {
            return array_values(
                array_filter(
                    $this->cachedProcessors[$cacheKey],
                    fn (TgTypeDTOProcessorContract $processor): bool => $processor
                        ->support($dto, $botConfig, $action)
                )
            );
        }

        // A matching command intercepts the update: only the command processor
        // runs, regular processors for this DTO are bypassed. When no command
        // matches (or the command is not declared), the regular flow takes over.
        $resolved = $commandName !== null
            ? $this->resolveCommandProcessors($commandName, $dto, $botConfig, $action, $chatId)
            : null;

        if ($resolved === null) {
            $resolved = [];
            $processors = $this->botSetup->processorRegistry
                ->get(
                    dto: $dto::class,
                    context: BotProcessorContext::fromBotSetup($this->botSetup),
                );

            foreach ($processors as $processor) {
                if (!$this->isModuleEnabled($processor, $botConfig->botId, $chatId)) {
                    continue;
                }

                if ($processor->support(
                    dto: $dto,
                    botConfig: $botConfig,
                    action: $action
                )) {
                    $resolved[] = $processor;
                }
            }
        }

        $this->cachedProcessors[$cacheKey] = $resolved;

        return $resolved;
    }

    /**
     * Resolve the exclusive processor for a slash command. Returns null when
     * the command is not declared in the registry (regular flow takes over)
     * or an empty array is never returned — an unknown/unfit command falls
     * back to the regular flow.
     *
     * @return list<TgTypeDTOProcessorContract>|null
     */
    private function resolveCommandProcessors(
        string $commandName,
        TgApiTypeDTOContract $dto,
        TgBotConfig $botConfig,
        ?string $action,
        ?int $chatId,
    ): ?array {
        $registry = $this->botSetup->commandRegistry;
        $processorClass = $registry?->processorOf($commandName);

        if ($processorClass === null) {
            return null;
        }

        /** @var TgTypeDTOProcessorContract $processor */
        $processor = $processorClass::build(
            BotProcessorContext::fromBotSetup($this->botSetup),
        );

        if (!$this->isModuleEnabled($processor, $botConfig->botId, $chatId)) {
            return null;
        }

        if (!$processor->support(dto: $dto, botConfig: $botConfig, action: $action)) {
            return null;
        }

        return [$processor];
    }

    /**
     * Command name of a message DTO text ("/cmd@bot arg"); null otherwise.
     */
    private function commandNameOf(TgApiTypeDTOContract $dto): ?string
    {
        if (!property_exists($dto, 'text')
            || !is_string($dto->text ?? null)
            || $dto->text === ''
        ) {
            return null;
        }

        return TgCommandRegistry::parseCommandName($dto->text);
    }

    /**
     * Module enablement filter: processors bound to a module are skipped when
     * the module is disabled for this (bot, chat). Global processors always pass.
     */
    private function isModuleEnabled(
        TgTypeDTOProcessorContract $processor,
        string $botId,
        ?int $chatId,
    ): bool {
        if ($this->moduleEnablement === null
            || !$processor instanceof TgModuleProcessorContract
            || $chatId === null
        ) {
            return true;
        }

        return $this->moduleEnablement->isEnabled($processor::moduleId(), $botId, $chatId);
    }

    /**
     * Telegram chat id of a DTO when it carries a chat; null otherwise.
     * ChatTypeDTO::$id is a numeric string in the generated DTO layer.
     */
    private function chatIdOf(TgApiTypeDTOContract $dto): ?int
    {
        if (property_exists($dto, 'chat')
            && isset($dto->chat)
            && $dto->chat instanceof ChatTypeDTO
        ) {
            return (int)$dto->chat->id;
        }

        return null;
    }
}
