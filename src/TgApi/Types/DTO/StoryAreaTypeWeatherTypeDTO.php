<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a story area containing weather information. Currently, a story can have up to 3 weather areas.')]
#[See('https://core.telegram.org/bots/api#storyareatypeweather')]
class StoryAreaTypeWeatherTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Temperature, in degree Celsius')]
        public string $temperature,
        #[Description('Emoji representing the weather')]
        public string $emoji,
        #[Description('A color of the area background in the ARGB format')]
        public int $backgroundColor,
        #[Description('Type of the area, always “weather”')]
        public string $type = 'weather',
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StoryAreaTypeWeather;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"weather"}],"nullable":false,"required":true},"temperature":{"property":"temperature","tgPropName":"temperature","types":["string"],"tgTypes":[{"type":"float"}],"nullable":false,"required":true},"emoji":{"property":"emoji","tgPropName":"emoji","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"background_color":{"property":"backgroundColor","tgPropName":"background_color","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
