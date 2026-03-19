<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a point on the map.')]
#[See('https://core.telegram.org/bots/api#location')]
class LocationTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Latitude as defined by the sender')]
        public string $latitude,
        #[Description('Longitude as defined by the sender')]
        public string $longitude,
        #[Description('The radius of uncertainty for the location, measured in meters; 0-1500')]
        public ?string $horizontalAccuracy = null,
        #[Description('Time relative to the message sending date, during which the location can be updated; in seconds. For active live locations only.')]
        public ?int $livePeriod = null,
        #[Description('The direction in which user is moving, in degrees; 1-360. For active live locations only.')]
        public ?int $heading = null,
        #[Description('The maximum distance for proximity alerts about approaching another chat member, in meters. For sent live locations only.')]
        public ?int $proximityAlertRadius = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Location;
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
{"latitude":{"property":"latitude","tgPropName":"latitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"longitude":{"property":"longitude","tgPropName":"longitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"horizontal_accuracy":{"property":"horizontalAccuracy","tgPropName":"horizontal_accuracy","types":["string"],"tgTypes":[{"type":"float"}],"nullable":true,"required":false},"live_period":{"property":"livePeriod","tgPropName":"live_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"heading":{"property":"heading","tgPropName":"heading","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"proximity_alert_radius":{"property":"proximityAlertRadius","tgPropName":"proximity_alert_radius","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
