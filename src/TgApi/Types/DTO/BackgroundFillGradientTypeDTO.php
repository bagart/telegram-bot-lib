<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The background is a gradient fill.')]
#[See('https://core.telegram.org/bots/api#backgroundfillgradient')]
class BackgroundFillGradientTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Top color of the gradient in the RGB24 format')]
        public int $topColor,
        #[Description('Bottom color of the gradient in the RGB24 format')]
        public int $bottomColor,
        #[Description('Clockwise rotation angle of the background fill in degrees; 0-359')]
        public int $rotationAngle,
        #[Description('Type of the background fill, always “gradient”')]
        public string $type = 'gradient',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BackgroundFillGradient;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"gradient"}],"nullable":false,"required":true},"top_color":{"property":"topColor","tgPropName":"top_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"bottom_color":{"property":"bottomColor","tgPropName":"bottom_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"rotation_angle":{"property":"rotationAngle","tgPropName":"rotation_angle","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
