<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ChatTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents an incoming inline query. When the user sends an empty query, your bot could return some default or trending results.')]
#[See('https://core.telegram.org/bots/api#inlinequery')]
class InlineQueryTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this query')]
        public string $id,
        #[Description('Sender')]
        public UserTypeDTO $from,
        #[Description('Text of the query (up to 256 characters)')]
        public string $query,
        #[Description('Offset of the results to be returned, can be controlled by the bot')]
        public string $offset,
        #[Description('Type of the chat from which the inline query was sent. Can be either “sender” for a private chat with the inline query sender, “private”, “group”, “supergroup”, or “channel”. The chat type should be always known for requests sent from official clients and most third-party clients, unless the request was sent from a secret chat')]
        public ?ChatTypeEnum $chatType = null,
        #[Description('Sender location, only for bots that request user location')]
        public ?LocationTypeDTO $location = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQuery;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"from":{"property":"from","tgPropName":"from","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"query":{"property":"query","tgPropName":"query","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"offset":{"property":"offset","tgPropName":"offset","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"chat_type":{"property":"chatType","tgPropName":"chat_type","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ChatTypeEnum"],"tgTypes":[{"type":"str","literal":"sender"},{"type":"str","literal":"private"},{"type":"str","literal":"group"},{"type":"str","literal":"supergroup"},{"type":"str","literal":"channel"}],"nullable":true,"required":false},"location":{"property":"location","tgPropName":"location","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LocationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Location"}],"nullable":true,"required":false}}
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
