<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The background is automatically filled based on the selected colors.')]
#[See('https://core.telegram.org/bots/api#backgroundtypefill')]
class BackgroundTypeFillTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The background fill')]
        public BackgroundFillTypeDTO $fill,
        #[Description('Dimming of the background in dark themes, as a percentage; 0-100')]
        public int $darkThemeDimming,
        #[Description('Type of the background, always “fill”')]
        public string $type = 'fill',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BackgroundTypeFill;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"fill"}],"nullable":false,"required":true},"fill":{"property":"fill","tgPropName":"fill","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BackgroundFillTypeDTO"],"tgTypes":[{"type":"api-type","name":"BackgroundFill"}],"nullable":false,"required":true},"dark_theme_dimming":{"property":"darkThemeDimming","tgPropName":"dark_theme_dimming","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
