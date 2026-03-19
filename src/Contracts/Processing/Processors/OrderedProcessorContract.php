<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Processors;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

interface OrderedProcessorContract
{
    public function executionKey(
        TgApiTypeDTOContract $dto,
    ): ?string;
}
