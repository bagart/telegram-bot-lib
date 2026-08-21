<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRule;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundMiddlewareRegistry;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRuleRegistry;
use BAGArt\TelegramBot\Processing\TypeDTOProcessorRegistry;
use LogicException;

/**
 * Registers module components into runtime registries. Accepts class-strings
 * only — lazy build via BotProcessorContext happens in the registry (C3).
 */
final class TypedModuleRegistrar implements TgModuleRegistrar
{
    public function __construct(
        private readonly TypeDTOProcessorRegistry $processorRegistry,
        private readonly ?MessageValidationRuleRegistry $ruleRegistry = null,
        private readonly ?OutboundMiddlewareRegistry $outboundMiddlewareRegistry = null,
        private readonly ?TgCommandRegistry $commandRegistry = null,
        private readonly ?AttributedComponentsScanner $attributedScanner = null,
    ) {
    }

    public function processor(string $dtoClass, string $processorClass): self
    {
        assert(
            is_a($processorClass, TgTypeDTOProcessorContract::class, true),
            "$processorClass must implement TgTypeDTOProcessorContract",
        );
        assert(
            is_a($dtoClass, TgApiTypeDTOContract::class, true),
            "$dtoClass must implement TgApiTypeDTOContract",
        );

        $this->processorRegistry->register($dtoClass, $processorClass);

        return $this;
    }

    public function validationRule(string $ruleClass, int $weight = 0): self
    {
        assert(
            is_a($ruleClass, MessageValidationRule::class, true),
            "$ruleClass must implement MessageValidationRule",
        );

        if ($this->ruleRegistry === null) {
            throw new LogicException(
                'validationRule() requires a MessageValidationRuleRegistry — '
                .'construct TypedModuleRegistrar with one to register rules.',
            );
        }

        $this->ruleRegistry->registerClass($ruleClass, $weight);

        return $this;
    }

    public function outboundMiddleware(string $middlewareClass): self
    {
        assert(
            is_a($middlewareClass, OutboundMiddleware::class, true),
            "$middlewareClass must implement OutboundMiddleware",
        );

        if ($this->outboundMiddlewareRegistry === null) {
            throw new LogicException(
                'outboundMiddleware() requires an OutboundMiddlewareRegistry — '
                .'construct TypedModuleRegistrar with one to register middleware.',
            );
        }

        $this->outboundMiddlewareRegistry->registerClass($middlewareClass);

        return $this;
    }

    public function command(string $name, string $processorClass): self
    {
        assert(
            is_a($processorClass, TgTypeDTOProcessorContract::class, true),
            "$processorClass must implement TgTypeDTOProcessorContract",
        );

        if ($this->commandRegistry === null) {
            throw new LogicException(
                'command() requires a TgCommandRegistry — '
                .'construct TypedModuleRegistrar with one to register commands.',
            );
        }

        $this->commandRegistry->register($name, $processorClass);

        return $this;
    }

    public function registerAttributed(string $providerClass): self
    {
        if ($this->attributedScanner === null) {
            throw new LogicException(
                'registerAttributed() requires an AttributedComponentsScanner — '
                .'construct TypedModuleRegistrar with one to use attribute declarations.',
            );
        }

        $this->attributedScanner->scanAndRegister($providerClass, $this);

        return $this;
    }
}
