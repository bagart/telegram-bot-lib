<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a location on a map. By default, the location will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the location.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultlocation')]
class InlineQueryResultLocationTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 Bytes')]
        public string $id,
        #[Description('Location latitude in degrees')]
        public string $latitude,
        #[Description('Location longitude in degrees')]
        public string $longitude,
        #[Description('Location title')]
        public string $title,
        #[Description('Type of the result, must be _location_')]
        public string $type = 'location',
        #[Description('The radius of uncertainty for the location, measured in meters; 0-1500')]
        public ?string $horizontalAccuracy = null,
        #[Description('Period in seconds during which the location can be updated, should be between 60 and 86400, or 0x7FFFFFFF for live locations that can be edited indefinitely.')]
        public ?int $livePeriod = null,
        #[Description('For live locations, a direction in which the user is moving, in degrees. Must be between 1 and 360 if specified.')]
        public ?int $heading = null,
        #[Description('For live locations, a maximum distance for proximity alerts about approaching another chat member, in meters. Must be between 1 and 100000 if specified.')]
        public ?int $proximityAlertRadius = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the location')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InputMessageContentTypeDTO $inputMessageContent = null,
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
        return TgApiTypesEnum::InlineQueryResultLocation;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"location"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"latitude":{"property":"latitude","tgPropName":"latitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"longitude":{"property":"longitude","tgPropName":"longitude","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"horizontal_accuracy":{"property":"horizontalAccuracy","tgPropName":"horizontal_accuracy","types":["string"],"tgTypes":[{"type":"float"}],"nullable":true,"required":false},"live_period":{"property":"livePeriod","tgPropName":"live_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"heading":{"property":"heading","tgPropName":"heading","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"proximity_alert_radius":{"property":"proximityAlertRadius","tgPropName":"proximity_alert_radius","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"thumbnail_width":{"property":"thumbnailWidth","tgPropName":"thumbnail_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"thumbnail_height":{"property":"thumbnailHeight","tgPropName":"thumbnail_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
