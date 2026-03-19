<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a clickable area on a story media.')]
#[See('https://core.telegram.org/bots/api#storyarea')]
class StoryAreaTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Position of the area')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaPositionTypeDTO $position,
        #[Description('Type of the area')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeTypeDTO $type,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StoryArea;
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
{"position":{"property":"position","tgPropName":"position","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryAreaPositionTypeDTO"],"tgTypes":[{"type":"api-type","name":"StoryAreaPosition"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryAreaTypeTypeDTO"],"tgTypes":[{"type":"api-type","name":"StoryAreaType"}],"nullable":false,"required":true}}
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
