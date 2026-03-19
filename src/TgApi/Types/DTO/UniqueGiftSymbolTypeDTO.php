<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the symbol shown on the pattern of a unique gift.')]
#[See('https://core.telegram.org/bots/api#uniquegiftsymbol')]
class UniqueGiftSymbolTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Name of the symbol')]
        public string $name,
        #[Description('The sticker that represents the unique gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\StickerTypeDTO $sticker,
        #[Description('The number of unique gifts that receive this model for every 1000 gifts upgraded')]
        public int $rarityPerMille,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UniqueGiftSymbol;
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
{"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"sticker":{"property":"sticker","tgPropName":"sticker","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StickerTypeDTO"],"tgTypes":[{"type":"api-type","name":"Sticker"}],"nullable":false,"required":true},"rarity_per_mille":{"property":"rarityPerMille","tgPropName":"rarity_per_mille","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
