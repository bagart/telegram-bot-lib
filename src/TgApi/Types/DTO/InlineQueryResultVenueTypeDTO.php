<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a venue. By default, the venue will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the venue.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultvenue')]
class InlineQueryResultVenueTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 Bytes')]
        public string $id,
        #[Description('Latitude of the venue location in degrees')]
        public string $latitude,
        #[Description('Longitude of the venue location in degrees')]
        public string $longitude,
        #[Description('Title of the venue')]
        public string $title,
        #[Description('Address of the venue')]
        public string $address,
        #[Description('Type of the result, must be _venue_')]
        public string $type = 'venue',
        #[Description('Foursquare identifier of the venue if known')]
        public ?string $foursquareId = null,
        #[Description('Foursquare type of the venue, if known. (For example, “arts\_entertainment/default”, “arts\_entertainment/aquarium” or “food/icecream”.)')]
        public ?string $foursquareType = null,
        #[Description('Google Places identifier of the venue')]
        public ?string $googlePlaceId = null,
        #[Description('Google Places type of the venue. (See [supported types](https://developers.google.com/places/web-service/supported_types).)')]
        public ?string $googlePlaceType = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the venue')]
        public ?InputMessageContentTypeDTO $inputMessageContent = null,
        #[Description('Url of the thumbnail for the result')]
        public ?string $thumbnailUrl = null,
        #[Description('Thumbnail width')]
        public ?int $thumbnailWidth = null,
        #[Description('Thumbnail height')]
        public ?int $thumbnailHeight = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultVenue;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"venue"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"latitude":{"property":"latitude","tgPropName":"latitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"longitude":{"property":"longitude","tgPropName":"longitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"address":{"property":"address","tgPropName":"address","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"foursquare_id":{"property":"foursquareId","tgPropName":"foursquare_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"foursquare_type":{"property":"foursquareType","tgPropName":"foursquare_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_id":{"property":"googlePlaceId","tgPropName":"google_place_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"google_place_type":{"property":"googlePlaceType","tgPropName":"google_place_type","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"thumbnail_width":{"property":"thumbnailWidth","tgPropName":"thumbnail_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"thumbnail_height":{"property":"thumbnailHeight","tgPropName":"thumbnail_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
