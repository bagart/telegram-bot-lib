<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRule;

/**
 * Single point through which a module declares its components.
 * Each method resolves to a strict contract — no duck-typing across boundaries.
 */
interface TgModuleRegistrar
{
    /**
     * @param  class-string<TgApiTypeDTOContract>  $dtoClass
     * @param  class-string<TgTypeDTOProcessorContract>  $processorClass
     */
    public function processor(string $dtoClass, string $processorClass): self;

    /**
     * @param  class-string<MessageValidationRule>  $ruleClass
     * @param  int  $weight  priority override; 0 keeps the rule's own priority()
     */
    public function validationRule(string $ruleClass, int $weight = 0): self;

    /**
     * @param  class-string<OutboundMiddleware>  $middlewareClass
     */
    public function outboundMiddleware(string $middlewareClass): self;

    /**
     * Declare a "/<name>" bot command routed exclusively to the processor.
     *
     * @param  class-string<TgTypeDTOProcessorContract>  $processorClass
     */
    public function command(string $name, string $processorClass): self;

    /**
     * Scan the module's own source directory for Tg* attributes and register
     * everything they declare. Explicit opt-in from inside register().
     *
     * @param  class-string<TgModuleContract>  $providerClass
     */
    public function registerAttributed(string $providerClass): self;

    /**
     * Declare a UI manifest class (raw store; owner = current module id).
     * Hard contract checks happen in the UI host module's refinement pass.
     *
     * @param  class-string  $uiClass
     */
    public function webUi(string $uiClass): self;

    /**
     * Declare a web API handler class (raw store; owner = current module id).
     *
     * @param  class-string  $handlerClass
     */
    public function webApi(string $handlerClass): self;

    /**
     * Declare a resource provider class (raw store; owner = current module id).
     *
     * @param  class-string  $providerClass
     */
    public function webResource(string $providerClass): self;

    /**
     * Declare a permission resolver class (raw store; owner = current module id).
     *
     * @param  class-string  $resolverClass
     */
    public function webPermissions(string $resolverClass): self;
}
