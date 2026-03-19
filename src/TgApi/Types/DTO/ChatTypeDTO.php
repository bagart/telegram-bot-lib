<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ChatPropTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a chat.')]
#[See('https://core.telegram.org/bots/api#chat')]
class ChatTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this chat.')]
        public string $id,
        #[Description('Type of the chat, can be either “private”, “group”, “supergroup” or “channel”')]
        public ChatPropTypeEnum $type,
        #[Description('Title, for supergroups, channels and group chats')]
        public ?string $title = null,
        #[Description('Username, for private chats, supergroups and channels if available')]
        public ?string $username = null,
        #[Description('First name of the other party in a private chat')]
        public ?string $firstName = null,
        #[Description('Last name of the other party in a private chat')]
        public ?string $lastName = null,
        #[Description('_True_, if the supergroup chat is a forum (has [topics](https://telegram.org/blog/topics-in-groups-collectible-usernames#topics-in-groups) enabled)')]
        public ?bool $isForum = true,
        #[Description('_True_, if the chat is the direct messages chat of a channel')]
        public ?bool $isDirectMessages = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Chat;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ChatPropTypeEnum"],"tgTypes":[{"type":"str","literal":"private"},{"type":"str","literal":"group"},{"type":"str","literal":"supergroup"},{"type":"str","literal":"channel"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"username":{"property":"username","tgPropName":"username","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"first_name":{"property":"firstName","tgPropName":"first_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"last_name":{"property":"lastName","tgPropName":"last_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"is_forum":{"property":"isForum","tgPropName":"is_forum","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_direct_messages":{"property":"isDirectMessages","tgPropName":"is_direct_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
