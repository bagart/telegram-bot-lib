<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityScopeEnumContract;

enum TgApiEntityScopeEnum: string implements TgApiEntityScopeEnumContract
{
    case Method = \BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum::class;

    case Type = \BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum::class;
}
