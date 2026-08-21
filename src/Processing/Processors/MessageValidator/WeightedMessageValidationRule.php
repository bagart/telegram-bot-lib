<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

/**
 * Decorator that overrides the priority of a wrapped rule with an explicit
 * weight, letting module declarations control rule order without editing
 * the rule itself.
 */
final readonly class WeightedMessageValidationRule implements MessageValidationRule
{
    public function __construct(
        private readonly MessageValidationRule $inner,
        private readonly int $weight,
    ) {
    }

    public function priority(): int
    {
        return $this->weight;
    }

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        return $this->inner->validate($dto);
    }
}
