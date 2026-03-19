<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a shipping address.')]
#[See('https://core.telegram.org/bots/api#shippingaddress')]
class ShippingAddressTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Two-letter [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) country code')]
        public string $countryCode,
        #[Description('State, if applicable')]
        public string $state,
        #[Description('City')]
        public string $city,
        #[Description('First line for the address')]
        public string $streetLine1,
        #[Description('Second line for the address')]
        public string $streetLine2,
        #[Description('Address post code')]
        public string $postCode,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ShippingAddress;
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
{"country_code":{"property":"countryCode","tgPropName":"country_code","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"state":{"property":"state","tgPropName":"state","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"city":{"property":"city","tgPropName":"city","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"street_line1":{"property":"streetLine1","tgPropName":"street_line1","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"street_line2":{"property":"streetLine2","tgPropName":"street_line2","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"post_code":{"property":"postCode","tgPropName":"post_code","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
