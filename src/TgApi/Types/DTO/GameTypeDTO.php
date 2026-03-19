<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a game. Use BotFather to create and edit games, their short names will act as unique identifiers.')]
#[See('https://core.telegram.org/bots/api#game')]
class GameTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Title of the game')]
        public string $title,
        #[Description('Description of the game')]
        public string $description,
        #[Description('Photo that will be displayed in the game message in chats.')]
        public array $photo,
        #[Description('Brief description of the game or high scores included in the game message. Can be automatically edited to include current high scores for the game when the bot calls [setGameScore](https://core.telegram.org/bots/api#setgamescore), or manually edited using [editMessageText](https://core.telegram.org/bots/api#editmessagetext). 0-4096 characters.')]
        public ?string $text = null,
        #[Description('Special entities that appear in _text_, such as usernames, URLs, bot commands, etc.')]
        public ?array $textEntities = null,
        #[Description('Animation that will be displayed in the game message in chats. Upload via [BotFather](https://t.me/botfather)')]
        public ?AnimationTypeDTO $animation = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Game;
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
{"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"photo":{"property":"photo","tgPropName":"photo","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PhotoSize"}}],"nullable":false,"required":true},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"text_entities":{"property":"textEntities","tgPropName":"text_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"animation":{"property":"animation","tgPropName":"animation","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AnimationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Animation"}],"nullable":true,"required":false}}
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
