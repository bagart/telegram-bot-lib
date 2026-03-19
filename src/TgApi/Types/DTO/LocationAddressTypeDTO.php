<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the physical address of a location.')]
#[See('https://core.telegram.org/bots/api#locationaddress')]
class LocationAddressTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The two-letter ISO 3166-1 alpha-2 country code of the country where the location is located')]
        public string $countryCode,
        #[Description('State of the location')]
        public ?string $state = null,
        #[Description('City of the location')]
        public ?string $city = null,
        #[Description('Street address of the location')]
        public ?string $street = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::LocationAddress;
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
{"country_code":{"property":"countryCode","tgPropName":"country_code","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"state":{"property":"state","tgPropName":"state","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"city":{"property":"city","tgPropName":"city","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"street":{"property":"street","tgPropName":"street","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
