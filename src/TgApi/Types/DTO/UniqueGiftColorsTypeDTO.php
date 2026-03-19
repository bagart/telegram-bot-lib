<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about the color scheme for a user"s name, message replies and link previews based on a unique gift.')]
#[See('https://core.telegram.org/bots/api#uniquegiftcolors')]
class UniqueGiftColorsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Custom emoji identifier of the unique gift"s model')]
        public string $modelCustomEmojiId,
        #[Description('Custom emoji identifier of the unique gift"s symbol')]
        public string $symbolCustomEmojiId,
        #[Description('Main color used in light themes; RGB format')]
        public int $lightThemeMainColor,
        #[Description('List of 1-3 additional colors used in light themes; RGB format')]
        public array $lightThemeOtherColors,
        #[Description('Main color used in dark themes; RGB format')]
        public int $darkThemeMainColor,
        #[Description('List of 1-3 additional colors used in dark themes; RGB format')]
        public array $darkThemeOtherColors,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UniqueGiftColors;
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
{"model_custom_emoji_id":{"property":"modelCustomEmojiId","tgPropName":"model_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"symbol_custom_emoji_id":{"property":"symbolCustomEmojiId","tgPropName":"symbol_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"light_theme_main_color":{"property":"lightThemeMainColor","tgPropName":"light_theme_main_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"light_theme_other_colors":{"property":"lightThemeOtherColors","tgPropName":"light_theme_other_colors","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":false,"required":true},"dark_theme_main_color":{"property":"darkThemeMainColor","tgPropName":"dark_theme_main_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"dark_theme_other_colors":{"property":"darkThemeOtherColors","tgPropName":"dark_theme_other_colors","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":false,"required":true}}
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
