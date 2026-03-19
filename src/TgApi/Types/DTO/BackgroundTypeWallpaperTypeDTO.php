<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The background is a wallpaper in the JPEG format.')]
#[See('https://core.telegram.org/bots/api#backgroundtypewallpaper')]
class BackgroundTypeWallpaperTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Document with the wallpaper')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\DocumentTypeDTO $document,
        #[Description('Dimming of the background in dark themes, as a percentage; 0-100')]
        public int $darkThemeDimming,
        #[Description('Type of the background, always “wallpaper”')]
        public string $type = 'wallpaper',
        #[Description('_True_, if the wallpaper is downscaled to fit in a 450x450 square and then box-blurred with radius 12')]
        public ?bool $isBlurred = true,
        #[Description('_True_, if the background moves slightly when the device is tilted')]
        public ?bool $isMoving = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BackgroundTypeWallpaper;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"wallpaper"}],"nullable":false,"required":true},"document":{"property":"document","tgPropName":"document","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DocumentTypeDTO"],"tgTypes":[{"type":"api-type","name":"Document"}],"nullable":false,"required":true},"dark_theme_dimming":{"property":"darkThemeDimming","tgPropName":"dark_theme_dimming","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_blurred":{"property":"isBlurred","tgPropName":"is_blurred","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_moving":{"property":"isMoving","tgPropName":"is_moving","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
