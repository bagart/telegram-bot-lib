<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules\Attributes;

use Attribute;

/**
 * Declares the class as a message validation rule. The class must still
 * implement MessageValidationRule — the attribute is sugar over
 * TgModuleRegistrar::validationRule(), not a contract replacement.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TgRuleAttribute
{
    public function __construct(
        public readonly int $weight = 0,
    ) {
    }
}
