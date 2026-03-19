<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the type of a clickable area on a story. Currently, it can be one of; ; -   [StoryAreaTypeLocation](https://core.telegram.org/bots/api#storyareatypelocation); -   [StoryAreaTypeSuggestedReaction](https://core.telegram.org/bots/api#storyareatypesuggestedreaction); -   [StoryAreaTypeLink](https://core.telegram.org/bots/api#storyareatypelink); -   [StoryAreaTypeWeather](https://core.telegram.org/bots/api#storyareatypeweather); -   [StoryAreaTypeUniqueGift](https://core.telegram.org/bots/api#storyareatypeuniquegift)')]
#[See('https://core.telegram.org/bots/api#storyareatype')]
class StoryAreaTypeTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiTypesEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StoryAreaType;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
