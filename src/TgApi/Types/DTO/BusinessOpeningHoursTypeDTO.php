<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the opening hours of a business.')]
#[See('https://core.telegram.org/bots/api#businessopeninghours')]
class BusinessOpeningHoursTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique name of the time zone for which the opening hours are defined')]
        public string $timeZoneName,
        #[Description('List of time intervals describing business opening hours')]
        public array $openingHours,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BusinessOpeningHours;
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
{"time_zone_name":{"property":"timeZoneName","tgPropName":"time_zone_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"opening_hours":{"property":"openingHours","tgPropName":"opening_hours","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessOpeningHoursIntervalTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"BusinessOpeningHoursInterval"}}],"nullable":false,"required":true}}
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
