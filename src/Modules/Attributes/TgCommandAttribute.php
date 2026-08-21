<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules\Attributes;

use Attribute;

/**
 * Declares the class as a "/<name>" bot command processor. The class must
 * still implement TgTypeDTOProcessorContract — the attribute is sugar over
 * TgModuleRegistrar::command(), not a contract replacement.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TgCommandAttribute
{
    /**
     * @param  string  $name  command name, with or without the leading slash
     *                        (normalized by the registry)
     */
    public function __construct(
        public readonly string $name,
    ) {
    }
}
