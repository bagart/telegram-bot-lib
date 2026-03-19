<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\StickerTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a sticker set.')]
#[See('https://core.telegram.org/bots/api#stickerset')]
class StickerSetTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Sticker set name')]
        public string $name,
        #[Description('Sticker set title')]
        public string $title,
        #[Description('Type of stickers in the set, currently one of “regular”, “mask”, “custom\_emoji”')]
        public StickerTypeEnum $stickerType,
        #[Description('List of all set stickers')]
        public array $stickers,
        #[Description('Sticker set thumbnail in the .WEBP, .TGS, or .WEBM format')]
        public ?PhotoSizeTypeDTO $thumbnail = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StickerSet;
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
{"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"sticker_type":{"property":"stickerType","tgPropName":"sticker_type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\StickerTypeEnum"],"tgTypes":[{"type":"str","literal":"regular"},{"type":"str","literal":"mask"},{"type":"str","literal":"custom_emoji"}],"nullable":false,"required":true},"stickers":{"property":"stickers","tgPropName":"stickers","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StickerTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"Sticker"}}],"nullable":false,"required":true},"thumbnail":{"property":"thumbnail","tgPropName":"thumbnail","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"],"tgTypes":[{"type":"api-type","name":"PhotoSize"}],"nullable":true,"required":false}}
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
