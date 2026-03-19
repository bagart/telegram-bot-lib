<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is contract but not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the origin of a message. It can be one of; ; -   [MessageOriginUser](https://core.telegram.org/bots/api#messageoriginuser); -   [MessageOriginHiddenUser](https://core.telegram.org/bots/api#messageoriginhiddenuser); -   [MessageOriginChat](https://core.telegram.org/bots/api#messageoriginchat); -   [MessageOriginChannel](https://core.telegram.org/bots/api#messageoriginchannel)')]
#[See('https://core.telegram.org/bots/api#messageorigin')]
class MessageOriginTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::MessageOrigin;
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
