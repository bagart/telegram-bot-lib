<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\TgApiServices;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiDTOContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract;

interface TgApiDTORegistryContract
{
    public function register(
        string|TgApiDTOContract $dtoClassName,
        ?TgApiEntityEnumContract $entityName = null,
        ?TgApiEntityScopeEnumContract $entityScope = null,
    ): void;

    /** @return TgApiDTOContract|string */
    public function getDTO(
        string|TgApiEntityEnumContract $tgEntityName,
        ?TgApiEntityScopeEnumContract $tgEntityScope = null,
    ): string;
}
