<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgUpdateProcessor;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

interface TgUpdateProcessorContract
{
    public function support(TgApiTypeDTOContract $dto): bool;

    public function process(TgApiTypeDTOContract $dto, string $botId): void;
}
