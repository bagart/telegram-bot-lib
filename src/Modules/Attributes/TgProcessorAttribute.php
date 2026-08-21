<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules\Attributes;

use Attribute;

/**
 * Declares the class as a DTO processor for the module's source directory
 * scan (AttributedComponentsScanner). The class must still implement
 * TgTypeDTOProcessorContract — the attribute is sugar over
 * TgModuleRegistrar::processor(), not a contract replacement.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TgProcessorAttribute
{
    /**
     * @param  class-string  $dto  DTO class the processor handles
     */
    public function __construct(
        public readonly string $dto,
    ) {
    }
}
