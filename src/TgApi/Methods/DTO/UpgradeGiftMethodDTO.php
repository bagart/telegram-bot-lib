<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Upgrades a given regular gift to a unique gift. Requires the _can\_transfer\_and\_upgrade\_gifts_ business bot right. Additionally requires the _can\_transfer\_stars_ business bot right if the upgrade is paid. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#upgradegift')]
class UpgradeGiftMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $businessConnectionId,
        #[Description('Unique identifier of the regular gift that should be upgraded to a unique one')]
        public string $ownedGiftId,
        #[Description('Pass _True_ to keep the original gift text, sender and receiver in the upgraded gift')]
        public ?bool $keepOriginalDetails = null,
        #[Description('The amount of Telegram Stars that will be paid for the upgrade from the business account balance. If `gift.prepaid_upgrade_star_count > 0`, then pass 0, otherwise, the _can\_transfer\_stars_ business bot right is required and `gift.upgrade_star_count` must be passed.')]
        public ?int $starCount = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::upgradeGift;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"owned_gift_id":{"property":"ownedGiftId","tgPropName":"owned_gift_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"keep_original_details":{"property":"keepOriginalDetails","tgPropName":"keep_original_details","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"star_count":{"property":"starCount","tgPropName":"star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
