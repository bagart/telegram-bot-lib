<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a venue.')]
#[See('https://core.telegram.org/bots/api#venue')]
class VenueTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Venue location. Can"t be a live location')]
        public LocationTypeDTO $location,
        #[Description('Name of the venue')]
        public string $title,
        #[Description('Address of the venue')]
        public string $address,
        #[Description('Foursquare identifier of the venue')]
        public ?string $foursquareId = null,
        #[Description('Foursquare type of the venue. (For example, “arts\_entertainment/default”, “arts\_entertainment/aquarium” or “food/icecream”.)')]
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
        return TgApiTypesEnum::Venue;
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
{"location":{"property":"location","tgPropName":"location","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LocationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Location"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"address":{"property":"address","tgPropName":"address","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"foursquare_id":{"property":"foursquareId","tgPropName":"foursquare_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"foursquare_type":{"property":"foursquareType","tgPropName":"foursquare_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_id":{"property":"googlePlaceId","tgPropName":"google_place_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_type":{"property":"googlePlaceType","tgPropName":"google_place_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
