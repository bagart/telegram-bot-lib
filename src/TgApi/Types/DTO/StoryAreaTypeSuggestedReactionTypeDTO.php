<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a story area pointing to a suggested reaction. Currently, a story can have up to 5 suggested reaction areas.')]
#[See('https://core.telegram.org/bots/api#storyareatypesuggestedreaction')]
class StoryAreaTypeSuggestedReactionTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Type of the reaction')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ReactionTypeTypeDTO $reactionType,
        #[Description('Type of the area, always “suggested\_reaction”')]
        public string $type = 'suggested_reaction',
        #[Description('Pass _True_ if the reaction area has a dark background')]
        public ?bool $isDark = null,
        #[Description('Pass _True_ if reaction area corner is flipped')]
        public ?bool $isFlipped = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StoryAreaTypeSuggestedReaction;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"suggested_reaction"}],"nullable":false,"required":true},"reaction_type":{"property":"reactionType","tgPropName":"reaction_type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReactionTypeTypeDTO"],"tgTypes":[{"type":"api-type","name":"ReactionType"}],"nullable":false,"required":true},"is_dark":{"property":"isDark","tgPropName":"is_dark","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"is_flipped":{"property":"isFlipped","tgPropName":"is_flipped","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
