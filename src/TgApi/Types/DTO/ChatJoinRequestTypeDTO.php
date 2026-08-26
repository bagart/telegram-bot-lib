<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a join request sent to a chat.')]
#[See('https://core.telegram.org/bots/api#chatjoinrequest')]
class ChatJoinRequestTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Chat to which the request was sent')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $chat,
        #[Description('User that sent the join request')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $from,
        #[Description('Identifier of a private chat with the user who sent the join request.  The bot can use this identifier for 5 minutes to send messages until the join request is processed, assuming no other administrator contacted the user.')]
        public string $userChatId,
        #[Description('Date the request was sent in Unix time')]
        public int $date,
        #[Description('Bio of the user.')]
        public ?string $bio = null,
        #[Description('Chat invite link that was used by the user to send the join request')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatInviteLinkTypeDTO $inviteLink = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatJoinRequest;
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
{"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"from":{"property":"from","tgPropName":"from","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"user_chat_id":{"property":"userChatId","tgPropName":"user_chat_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"bio":{"property":"bio","tgPropName":"bio","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"invite_link":{"property":"inviteLink","tgPropName":"invite_link","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatInviteLinkTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatInviteLink"}],"nullable":true,"required":false}}
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
