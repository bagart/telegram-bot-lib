<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about a chat that was shared with the bot using a [KeyboardButtonRequestChat](https://core.telegram.org/bots/api#keyboardbuttonrequestchat) button.')]
#[See('https://core.telegram.org/bots/api#chatshared')]
class ChatSharedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the request')]
        public int $requestId,
        #[Description('Identifier of the shared chat.  The bot may not have access to the chat and could be unable to use this identifier, unless the chat is already known to the bot by some other means.')]
        public string $chatId,
        #[Description('Title of the chat, if the title was requested by the bot.')]
        public ?string $title = null,
        #[Description('Username of the chat, if the username was requested by the bot and available.')]
        public ?string $username = null,
        #[Description('Available sizes of the chat photo, if the photo was requested by the bot')]
        public ?array $photo = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatShared;
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
{"request_id":{"property":"requestId","tgPropName":"request_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"username":{"property":"username","tgPropName":"username","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo":{"property":"photo","tgPropName":"photo","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PhotoSize"}}],"nullable":true,"required":false}}
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
