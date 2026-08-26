<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents an inline button that switches the current user to inline mode in a chosen chat, with an optional default inline query.')]
#[See('https://core.telegram.org/bots/api#switchinlinequerychosenchat')]
class SwitchInlineQueryChosenChatTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The default inline query to be inserted in the input field. If left empty, only the bot"s username will be inserted')]
        public ?string $query = null,
        #[Description('_True_, if private chats with users can be chosen')]
        public ?bool $allowUserChats = null,
        #[Description('_True_, if private chats with bots can be chosen')]
        public ?bool $allowBotChats = null,
        #[Description('_True_, if group and supergroup chats can be chosen')]
        public ?bool $allowGroupChats = null,
        #[Description('_True_, if channel chats can be chosen')]
        public ?bool $allowChannelChats = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::SwitchInlineQueryChosenChat;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"query":{"property":"query","tgPropName":"query","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"allow_user_chats":{"property":"allowUserChats","tgPropName":"allow_user_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_bot_chats":{"property":"allowBotChats","tgPropName":"allow_bot_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_group_chats":{"property":"allowGroupChats","tgPropName":"allow_group_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_channel_chats":{"property":"allowChannelChats","tgPropName":"allow_channel_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
XJSON,
            true,
            20,
            JSON_THROW_ON_ERROR
        );

        $result = [];
        foreach ($metaByProp as $tgPropName => $propertyMeta) {
            $result[$tgPropName] = new TgApiProperty(...$propertyMeta);
        }

        return $result;
    }
}
