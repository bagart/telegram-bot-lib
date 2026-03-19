<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a venue message to be sent as the result of an inline query.')]
#[See('https://core.telegram.org/bots/api#inputvenuemessagecontent')]
class InputVenueMessageContentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Latitude of the venue in degrees')]
        public string $latitude,
        #[Description('Longitude of the venue in degrees')]
        public string $longitude,
        #[Description('Name of the venue')]
        public string $title,
        #[Description('Address of the venue')]
        public string $address,
        #[Description('Foursquare identifier of the venue, if known')]
        public ?string $foursquareId = null,
        #[Description('Foursquare type of the venue, if known. (For example, “arts\_entertainment/default”, “arts\_entertainment/aquarium” or “food/icecream”.)')]
        public ?string $foursquareType = null,
        #[Description('Google Places identifier of the venue')]
        public ?string $googlePlaceId = null,
        #[Description('Google Places type of the venue. (See [supported types](https://developers.google.com/places/web-service/supported_types).)')]
        public ?string $googlePlaceType = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputVenueMessageContent;
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
{"latitude":{"property":"latitude","tgPropName":"latitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"longitude":{"property":"longitude","tgPropName":"longitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"address":{"property":"address","tgPropName":"address","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"foursquare_id":{"property":"foursquareId","tgPropName":"foursquare_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"foursquare_type":{"property":"foursquareType","tgPropName":"foursquare_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_id":{"property":"googlePlaceId","tgPropName":"google_place_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_type":{"property":"googlePlaceType","tgPropName":"google_place_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
