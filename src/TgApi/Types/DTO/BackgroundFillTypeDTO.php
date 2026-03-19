<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the way a background is filled based on the selected colors. Currently, it can be one of; ; -   [BackgroundFillSolid](https://core.telegram.org/bots/api#backgroundfillsolid); -   [BackgroundFillGradient](https://core.telegram.org/bots/api#backgroundfillgradient); -   [BackgroundFillFreeformGradient](https://core.telegram.org/bots/api#backgroundfillfreeformgradient)')]
#[See('https://core.telegram.org/bots/api#backgroundfill')]
class BackgroundFillTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::BackgroundFill;
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
