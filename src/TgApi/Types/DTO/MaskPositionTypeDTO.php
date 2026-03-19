<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the position on faces where a mask should be placed by default.')]
#[See('https://core.telegram.org/bots/api#maskposition')]
class MaskPositionTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The part of the face relative to which the mask should be placed. One of “forehead”, “eyes”, “mouth”, or “chin”.')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\PointEnum $point,
        #[Description('Shift by X-axis measured in widths of the mask scaled to the face size, from left to right. For example, choosing -1.0 will place mask just to the left of the default mask position.')]
        public string $xShift,
        #[Description('Shift by Y-axis measured in heights of the mask scaled to the face size, from top to bottom. For example, 1.0 will place the mask just below the default mask position.')]
        public string $yShift,
        #[Description('Mask scaling coefficient. For example, 2.0 means double size.')]
        public string $scale,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::MaskPosition;
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
{"point":{"property":"point","tgPropName":"point","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\PointEnum"],"tgTypes":[{"type":"str","literal":"forehead"},{"type":"str","literal":"eyes"},{"type":"str","literal":"mouth"},{"type":"str","literal":"chin"}],"nullable":false,"required":true},"x_shift":{"property":"xShift","tgPropName":"x_shift","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"y_shift":{"property":"yShift","tgPropName":"y_shift","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"scale":{"property":"scale","tgPropName":"scale","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true}}
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
