<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

trait TgProcessorDefaultTrait
{
    public function isStrictOrdered(
        TgApiTypeDTOContract $dto,
        \BAGArt\TelegramBot\Configs\TgBotConfig $botConfig,
        ?string $action = null,
    ): bool {
        return false;
    }

    public function isNeedUpdateDTO(): bool
    {
        return false;
    }

    public function executionKey(
        TgApiTypeDTOContract $dto,
    ): ?string {
        return null;
    }
}
