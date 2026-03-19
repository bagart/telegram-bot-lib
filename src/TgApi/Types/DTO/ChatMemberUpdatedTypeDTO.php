<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents changes in the status of a chat member.')]
#[See('https://core.telegram.org/bots/api#chatmemberupdated')]
class ChatMemberUpdatedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Chat the user belongs to')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $chat,
        #[Description('Performer of the action, which resulted in the change')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $from,
        #[Description('Date the change was done in Unix time')]
        public int $date,
        #[Description('Previous information about the chat member')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberTypeDTO $oldChatMember,
        #[Description('New information about the chat member')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberTypeDTO $newChatMember,
        #[Description('Chat invite link, which was used by the user to join the chat; for joining by invite link events only.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatInviteLinkTypeDTO $inviteLink = null,
        #[Description('_True_, if the user joined the chat after sending a direct join request without using an invite link and being approved by an administrator')]
        public ?bool $viaJoinRequest = null,
        #[Description('_True_, if the user joined the chat via a chat folder invite link')]
        public ?bool $viaChatFolderInviteLink = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatMemberUpdated;
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
{"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"from":{"property":"from","tgPropName":"from","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"old_chat_member":{"property":"oldChatMember","tgPropName":"old_chat_member","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatMemberTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatMember"}],"nullable":false,"required":true},"new_chat_member":{"property":"newChatMember","tgPropName":"new_chat_member","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatMemberTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatMember"}],"nullable":false,"required":true},"invite_link":{"property":"inviteLink","tgPropName":"invite_link","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatInviteLinkTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatInviteLink"}],"nullable":true,"required":false},"via_join_request":{"property":"viaJoinRequest","tgPropName":"via_join_request","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"via_chat_folder_invite_link":{"property":"viaChatFolderInviteLink","tgPropName":"via_chat_folder_invite_link","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
