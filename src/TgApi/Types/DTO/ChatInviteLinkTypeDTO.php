<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents an invite link for a chat.')]
#[See('https://core.telegram.org/bots/api#chatinvitelink')]
class ChatInviteLinkTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The invite link. If the link was created by another chat administrator, then the second part of the link will be replaced with “…”.')]
        public string $inviteLink,
        #[Description('Creator of the link')]
        public UserTypeDTO $creator,
        #[Description('_True_, if users joining the chat via the link need to be approved by chat administrators')]
        public bool $createsJoinRequest,
        #[Description('_True_, if the link is primary')]
        public bool $isPrimary,
        #[Description('_True_, if the link is revoked')]
        public bool $isRevoked,
        #[Description('Invite link name')]
        public ?string $name = null,
        #[Description('Point in time (Unix timestamp) when the link will expire or has been expired')]
        public ?int $expireDate = null,
        #[Description('The maximum number of users that can be members of the chat simultaneously after joining the chat via this invite link; 1-99999')]
        public ?int $memberLimit = null,
        #[Description('Number of pending join requests created using this link')]
        public ?int $pendingJoinRequestCount = null,
        #[Description('The number of seconds the subscription will be active for before the next payment')]
        public ?int $subscriptionPeriod = null,
        #[Description('The amount of Telegram Stars a user must pay initially and after each subsequent subscription period to be a member of the chat using the link')]
        public ?int $subscriptionPrice = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatInviteLink;
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
{"invite_link":{"property":"inviteLink","tgPropName":"invite_link","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"creator":{"property":"creator","tgPropName":"creator","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"creates_join_request":{"property":"createsJoinRequest","tgPropName":"creates_join_request","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"is_primary":{"property":"isPrimary","tgPropName":"is_primary","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"is_revoked":{"property":"isRevoked","tgPropName":"is_revoked","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"expire_date":{"property":"expireDate","tgPropName":"expire_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"member_limit":{"property":"memberLimit","tgPropName":"member_limit","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"pending_join_request_count":{"property":"pendingJoinRequestCount","tgPropName":"pending_join_request_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"subscription_period":{"property":"subscriptionPeriod","tgPropName":"subscription_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"subscription_price":{"property":"subscriptionPrice","tgPropName":"subscription_price","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
