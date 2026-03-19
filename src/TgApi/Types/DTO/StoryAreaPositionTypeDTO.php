<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the position of a clickable area within a story.')]
#[See('https://core.telegram.org/bots/api#storyareaposition')]
class StoryAreaPositionTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The abscissa of the area"s center, as a percentage of the media width')]
        public string $xPercentage,
        #[Description('The ordinate of the area"s center, as a percentage of the media height')]
        public string $yPercentage,
        #[Description('The width of the area"s rectangle, as a percentage of the media width')]
        public string $widthPercentage,
        #[Description('The height of the area"s rectangle, as a percentage of the media height')]
        public string $heightPercentage,
        #[Description('The clockwise rotation angle of the rectangle, in degrees; 0-360')]
        public string $rotationAngle,
        #[Description('The radius of the rectangle corner rounding, as a percentage of the media width')]
        public string $cornerRadiusPercentage,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StoryAreaPosition;
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
{"x_percentage":{"property":"xPercentage","tgPropName":"x_percentage","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"y_percentage":{"property":"yPercentage","tgPropName":"y_percentage","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"width_percentage":{"property":"widthPercentage","tgPropName":"width_percentage","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"height_percentage":{"property":"heightPercentage","tgPropName":"height_percentage","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"rotation_angle":{"property":"rotationAngle","tgPropName":"rotation_angle","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"corner_radius_percentage":{"property":"cornerRadiusPercentage","tgPropName":"corner_radius_percentage","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true}}
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
