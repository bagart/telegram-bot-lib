<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\ChatInviteLinkTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to create a [subscription invite link](https://telegram.org/blog/superchannels-star-reactions-subscriptions#star-subscriptions) for a channel chat. The bot must have the _can\_invite\_users_ administrator rights. The link can be edited using the method [editChatSubscriptionInviteLink](https://core.telegram.org/bots/api#editchatsubscriptioninvitelink) or revoked using the method [revokeChatInviteLink](https://core.telegram.org/bots/api#revokechatinvitelink). Returns the new invite link as a [ChatInviteLink](https://core.telegram.org/bots/api#chatinvitelink) object.')]
#[See('https://core.telegram.org/bots/api#createchatsubscriptioninvitelink')]
class CreateChatSubscriptionInviteLinkMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target channel chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
        #[Description('The amount of Telegram Stars a user must pay initially and after each subsequent subscription period to be a member of the chat; 1-10000')]
        public int $subscriptionPrice,
        #[Description('Invite link name; 0-32 characters')]
        public ?string $name = null,
        #[Description('The number of seconds the subscription will be active for before the next payment. Currently, it must always be 2592000 (30 days).')]
        public int $subscriptionPeriod = 2592000,
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
        return TgApiMethodsEnum::createChatSubscriptionInviteLink;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"name":{"property":"name","tgPropName":"name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"subscription_period":{"property":"subscriptionPeriod","tgPropName":"subscription_period","types":["int"],"tgTypes":[{"type":"int32","literal":2592000}],"nullable":false,"required":true},"subscription_price":{"property":"subscriptionPrice","tgPropName":"subscription_price","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
