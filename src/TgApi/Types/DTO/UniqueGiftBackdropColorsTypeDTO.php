<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the colors of the backdrop of a unique gift.')]
#[See('https://core.telegram.org/bots/api#uniquegiftbackdropcolors')]
class UniqueGiftBackdropColorsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The color in the center of the backdrop in RGB format')]
        public int $centerColor,
        #[Description('The color on the edges of the backdrop in RGB format')]
        public int $edgeColor,
        #[Description('The color to be applied to the symbol in RGB format')]
        public int $symbolColor,
        #[Description('The color for the text on the backdrop in RGB format')]
        public int $textColor,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UniqueGiftBackdropColors;
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
{"center_color":{"property":"centerColor","tgPropName":"center_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"edge_color":{"property":"edgeColor","tgPropName":"edge_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"symbol_color":{"property":"symbolColor","tgPropName":"symbol_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"text_color":{"property":"textColor","tgPropName":"text_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
