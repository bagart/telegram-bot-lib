<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is contract but not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the source of a chat boost. It can be one of; ; -   [ChatBoostSourcePremium](https://core.telegram.org/bots/api#chatboostsourcepremium); -   [ChatBoostSourceGiftCode](https://core.telegram.org/bots/api#chatboostsourcegiftcode); -   [ChatBoostSourceGiveaway](https://core.telegram.org/bots/api#chatboostsourcegiveaway)')]
#[See('https://core.telegram.org/bots/api#chatboostsource')]
class ChatBoostSourceTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::ChatBoostSource;
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
