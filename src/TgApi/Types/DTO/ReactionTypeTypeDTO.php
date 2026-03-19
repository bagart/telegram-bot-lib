<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the type of a reaction. Currently, it can be one of; ; -   [ReactionTypeEmoji](https://core.telegram.org/bots/api#reactiontypeemoji); -   [ReactionTypeCustomEmoji](https://core.telegram.org/bots/api#reactiontypecustomemoji); -   [ReactionTypePaid](https://core.telegram.org/bots/api#reactiontypepaid)')]
#[See('https://core.telegram.org/bots/api#reactiontype')]
class ReactionTypeTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::ReactionType;
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
