<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

enum TgApiEntityScopeEnum: string implements TgApiEntityScopeEnumContract
{
    case Method = TgApiMethodsEnum::class;

    case Type = TgApiTypesEnum::class;
}
