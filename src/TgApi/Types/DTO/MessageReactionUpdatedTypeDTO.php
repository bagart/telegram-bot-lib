<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a change of a reaction on a message performed by a user.')]
#[See('https://core.telegram.org/bots/api#messagereactionupdated')]
class MessageReactionUpdatedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The chat containing the message the user reacted to')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $chat,
        #[Description('Unique identifier of the message inside the chat')]
        public int $messageId,
        #[Description('Date of the change in Unix time')]
        public int $date,
        #[Description('Previous list of reaction types that were set by the user')]
        public array $oldReaction,
        #[Description('New list of reaction types that have been set by the user')]
        public array $newReaction,
        #[Description('The user that changed the reaction, if the user isn"t anonymous')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user = null,
        #[Description('The chat on behalf of which the reaction was changed, if the user is anonymous')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $actorChat = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::MessageReactionUpdated;
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
{"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"actor_chat":{"property":"actorChat","tgPropName":"actor_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"old_reaction":{"property":"oldReaction","tgPropName":"old_reaction","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReactionTypeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"ReactionType"}}],"nullable":false,"required":true},"new_reaction":{"property":"newReaction","tgPropName":"new_reaction","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReactionTypeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"ReactionType"}}],"nullable":false,"required":true}}
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
