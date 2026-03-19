<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the types of gifts that can be gifted to a user or a chat.')]
#[See('https://core.telegram.org/bots/api#acceptedgifttypes')]
class AcceptedGiftTypesTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('_True_, if unlimited regular gifts are accepted')]
        public bool $unlimitedGifts,
        #[Description('_True_, if limited regular gifts are accepted')]
        public bool $limitedGifts,
        #[Description('_True_, if unique gifts or gifts that can be upgraded to unique for free are accepted')]
        public bool $uniqueGifts,
        #[Description('_True_, if a Telegram Premium subscription is accepted')]
        public bool $premiumSubscription,
        #[Description('_True_, if transfers of unique gifts from channels are accepted')]
        public bool $giftsFromChannels,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::AcceptedGiftTypes;
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
{"unlimited_gifts":{"property":"unlimitedGifts","tgPropName":"unlimited_gifts","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"limited_gifts":{"property":"limitedGifts","tgPropName":"limited_gifts","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"unique_gifts":{"property":"uniqueGifts","tgPropName":"unique_gifts","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"premium_subscription":{"property":"premiumSubscription","tgPropName":"premium_subscription","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"gifts_from_channels":{"property":"giftsFromChannels","tgPropName":"gifts_from_channels","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true}}
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
