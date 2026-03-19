<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents the scope to which bot commands are applied. Currently, the following 7 scopes are supported:; ; -   [BotCommandScopeDefault](https://core.telegram.org/bots/api#botcommandscopedefault); -   [BotCommandScopeAllPrivateChats](https://core.telegram.org/bots/api#botcommandscopeallprivatechats); -   [BotCommandScopeAllGroupChats](https://core.telegram.org/bots/api#botcommandscopeallgroupchats); -   [BotCommandScopeAllChatAdministrators](https://core.telegram.org/bots/api#botcommandscopeallchatadministrators); -   [BotCommandScopeChat](https://core.telegram.org/bots/api#botcommandscopechat); -   [BotCommandScopeChatAdministrators](https://core.telegram.org/bots/api#botcommandscopechatadministrators); -   [BotCommandScopeChatMember](https://core.telegram.org/bots/api#botcommandscopechatmember)')]
#[See('https://core.telegram.org/bots/api#botcommandscope')]
class BotCommandScopeTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::BotCommandScope;
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
