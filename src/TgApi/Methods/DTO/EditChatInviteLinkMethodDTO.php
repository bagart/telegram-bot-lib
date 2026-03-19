<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatInviteLinkTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to edit a non-primary invite link created by the bot. The bot must be an administrator in the chat for this to work and must have the appropriate administrator rights. Returns the edited invite link as a [ChatInviteLink](https://core.telegram.org/bots/api#chatinvitelink) object.')]
#[See('https://core.telegram.org/bots/api#editchatinvitelink')]
class EditChatInviteLinkMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
        #[Description('The invite link to edit')]
        public string $inviteLink,
        #[Description('Invite link name; 0-32 characters')]
        public ?string $name = null,
        #[Description('Point in time (Unix timestamp) when the link will expire')]
        public ?int $expireDate = null,
        #[Description('The maximum number of users that can be members of the chat simultaneously after joining the chat via this invite link; 1-99999')]
        public ?int $memberLimit = null,
        #[Description('_True_, if users joining the chat via the link need to be approved by chat administrators. If _True_, _member\_limit_ can"t be specified')]
        public ?bool $createsJoinRequest = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            ChatInviteLinkTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::editChatInviteLink;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"invite_link":{"property":"inviteLink","tgPropName":"invite_link","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"expire_date":{"property":"expireDate","tgPropName":"expire_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"member_limit":{"property":"memberLimit","tgPropName":"member_limit","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"creates_join_request":{"property":"createsJoinRequest","tgPropName":"creates_join_request","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
