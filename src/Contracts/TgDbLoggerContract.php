<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

interface TgDbLoggerContract
{
    public function log(TgApiTypeDTOContract $dto, array $extra = []): void;

    public function setTableName(string $tableName): void;
}
