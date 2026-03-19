<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The background is a .PNG or .TGV (gzipped subset of SVG with MIME type “application/x-tgwallpattern”) pattern to be combined with the background fill chosen by the user.')]
#[See('https://core.telegram.org/bots/api#backgroundtypepattern')]
class BackgroundTypePatternTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Document with the pattern')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\DocumentTypeDTO $document,
        #[Description('The background fill that is combined with the pattern')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundFillTypeDTO $fill,
        #[Description('Intensity of the pattern when it is shown above the filled background; 0-100')]
        public int $intensity,
        #[Description('Type of the background, always “pattern”')]
        public string $type = 'pattern',
        #[Description('_True_, if the background fill must be applied only to the pattern itself. All other pixels are black in this case. For dark themes only')]
        public ?bool $isInverted = true,
        #[Description('_True_, if the background moves slightly when the device is tilted')]
        public ?bool $isMoving = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BackgroundTypePattern;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"pattern"}],"nullable":false,"required":true},"document":{"property":"document","tgPropName":"document","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DocumentTypeDTO"],"tgTypes":[{"type":"api-type","name":"Document"}],"nullable":false,"required":true},"fill":{"property":"fill","tgPropName":"fill","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BackgroundFillTypeDTO"],"tgTypes":[{"type":"api-type","name":"BackgroundFill"}],"nullable":false,"required":true},"intensity":{"property":"intensity","tgPropName":"intensity","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_inverted":{"property":"isInverted","tgPropName":"is_inverted","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_moving":{"property":"isMoving","tgPropName":"is_moving","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
