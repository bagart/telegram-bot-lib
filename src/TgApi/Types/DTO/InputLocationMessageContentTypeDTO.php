<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a location message to be sent as the result of an inline query.')]
#[See('https://core.telegram.org/bots/api#inputlocationmessagecontent')]
class InputLocationMessageContentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Latitude of the location in degrees')]
        public string $latitude,
        #[Description('Longitude of the location in degrees')]
        public string $longitude,
        #[Description('The radius of uncertainty for the location, measured in meters; 0-1500')]
        public ?string $horizontalAccuracy = null,
        #[Description('Period in seconds during which the location can be updated, should be between 60 and 86400, or 0x7FFFFFFF for live locations that can be edited indefinitely.')]
        public ?int $livePeriod = null,
        #[Description('For live locations, a direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.')]
        public ?int $heading = null,
        #[Description('For live locations, a maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.')]
        public ?int $proximityAlertRadius = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputLocationMessageContent;
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
