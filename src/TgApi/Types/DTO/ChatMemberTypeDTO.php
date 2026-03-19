<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is contract but not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about one member of a chat. Currently, the following 6 types of chat members are supported:; ; -   [ChatMemberOwner](https://core.telegram.org/bots/api#chatmemberowner); -   [ChatMemberAdministrator](https://core.telegram.org/bots/api#chatmemberadministrator); -   [ChatMemberMember](https://core.telegram.org/bots/api#chatmembermember); -   [ChatMemberRestricted](https://core.telegram.org/bots/api#chatmemberrestricted); -   [ChatMemberLeft](https://core.telegram.org/bots/api#chatmemberleft); -   [ChatMemberBanned](https://core.telegram.org/bots/api#chatmemberbanned)')]
#[See('https://core.telegram.org/bots/api#chatmember')]
class ChatMemberTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::ChatMember;
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
