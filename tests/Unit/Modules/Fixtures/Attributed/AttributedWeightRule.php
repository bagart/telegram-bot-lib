<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Modules\Fixtures\Attributed;

use BAGArt\TelegramBot\Modules\Attributes\TgRuleAttribute;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationRule;
use BAGArt\TelegramBot\Processing\Processors\MessageValidator\MessageValidationVerdict;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

#[TgRuleAttribute(weight: 7)]
class AttributedWeightRule implements MessageValidationRule
{
    public function priority(): int
    {
        return 3;
    }

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        return null;
    }
}
