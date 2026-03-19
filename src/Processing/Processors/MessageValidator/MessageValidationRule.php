<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;

interface MessageValidationRule
{
    public function priority(): int;

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict;
}
