<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the backdrop of a unique gift.')]
#[See('https://core.telegram.org/bots/api#uniquegiftbackdrop')]
class UniqueGiftBackdropTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Name of the backdrop')]
        public string $name,
        #[Description('Colors of the backdrop')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftBackdropColorsTypeDTO $colors,
        #[Description('The number of unique gifts that receive this backdrop for every 1000 gifts upgraded')]
        public int $rarityPerMille,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UniqueGiftBackdrop;
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
{"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"colors":{"property":"colors","tgPropName":"colors","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftBackdropColorsTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftBackdropColors"}],"nullable":false,"required":true},"rarity_per_mille":{"property":"rarityPerMille","tgPropName":"rarity_per_mille","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
