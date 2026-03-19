<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Contains the list of gifts received and owned by a user or a chat.')]
#[See('https://core.telegram.org/bots/api#ownedgifts')]
class OwnedGiftsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The total number of gifts owned by the user or the chat')]
        public int $totalCount,
        #[Description('The list of gifts')]
        public array $gifts,
        #[Description('Offset for the next request. If empty, then there are no more results')]
        public ?string $nextOffset = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::OwnedGifts;
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
{"total_count":{"property":"totalCount","tgPropName":"total_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"gifts":{"property":"gifts","tgPropName":"gifts","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\OwnedGiftTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"OwnedGift"}}],"nullable":false,"required":true},"next_offset":{"property":"nextOffset","tgPropName":"next_offset","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
