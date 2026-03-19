<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the type of a background. Currently, it can be one of; ; -   [BackgroundTypeFill](https://core.telegram.org/bots/api#backgroundtypefill); -   [BackgroundTypeWallpaper](https://core.telegram.org/bots/api#backgroundtypewallpaper); -   [BackgroundTypePattern](https://core.telegram.org/bots/api#backgroundtypepattern); -   [BackgroundTypeChatTheme](https://core.telegram.org/bots/api#backgroundtypechattheme)')]
#[See('https://core.telegram.org/bots/api#backgroundtype')]
class BackgroundTypeTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiTypesEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BackgroundType;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
