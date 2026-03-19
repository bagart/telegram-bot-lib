<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to promote or demote a user in a supergroup or a channel. The bot must be an administrator in the chat for this to work and must have the appropriate administrator rights. Pass _False_ for all boolean parameters to demote a user. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#promotechatmember')]
class PromoteChatMemberMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
        #[Description('Unique identifier of the target user')]
        public int $userId,
        #[Description('Pass _True_ if the administrator"s presence in the chat is hidden')]
        public ?bool $isAnonymous = null,
        #[Description('Pass _True_ if the administrator can access the chat event log, get boost list, see hidden supergroup and channel members, report spam messages, ignore slow mode, and send messages to the chat without paying Telegram Stars. Implied by any other administrator privilege.')]
        public ?bool $canManageChat = null,
        #[Description('Pass _True_ if the administrator can delete messages of other users')]
        public ?bool $canDeleteMessages = null,
        #[Description('Pass _True_ if the administrator can manage video chats')]
        public ?bool $canManageVideoChats = null,
        #[Description('Pass _True_ if the administrator can restrict, ban or unban chat members, or access supergroup statistics. For backward compatibility, defaults to _True_ for promotions of channel administrators')]
        public ?bool $canRestrictMembers = null,
        #[Description('Pass _True_ if the administrator can add new administrators with a subset of their own privileges or demote administrators that they have promoted, directly or indirectly (promoted by administrators that were appointed by him)')]
        public ?bool $canPromoteMembers = null,
        #[Description('Pass _True_ if the administrator can change chat title, photo and other settings')]
        public ?bool $canChangeInfo = null,
        #[Description('Pass _True_ if the administrator can invite new users to the chat')]
        public ?bool $canInviteUsers = null,
        #[Description('Pass _True_ if the administrator can post stories to the chat')]
        public ?bool $canPostStories = null,
        #[Description('Pass _True_ if the administrator can edit stories posted by other users, post stories to the chat page, pin chat stories, and access the chat"s story archive')]
        public ?bool $canEditStories = null,
        #[Description('Pass _True_ if the administrator can delete stories posted by other users')]
        public ?bool $canDeleteStories = null,
        #[Description('Pass _True_ if the administrator can post messages in the channel, approve suggested posts, or access channel statistics; for channels only')]
        public ?bool $canPostMessages = null,
        #[Description('Pass _True_ if the administrator can edit messages of other users and can pin messages; for channels only')]
        public ?bool $canEditMessages = null,
        #[Description('Pass _True_ if the administrator can pin messages; for supergroups only')]
        public ?bool $canPinMessages = null,
        #[Description('Pass _True_ if the user is allowed to create, rename, close, and reopen forum topics; for supergroups only')]
        public ?bool $canManageTopics = null,
        #[Description('Pass _True_ if the administrator can manage direct messages within the channel and decline suggested posts; for channels only')]
        public ?bool $canManageDirectMessages = null,
        #[Description('Pass _True_ if the administrator can edit the tags of regular members; for groups and supergroups only')]
        public ?bool $canManageTags = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::promoteChatMember;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"is_anonymous":{"property":"isAnonymous","tgPropName":"is_anonymous","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_chat":{"property":"canManageChat","tgPropName":"can_manage_chat","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_delete_messages":{"property":"canDeleteMessages","tgPropName":"can_delete_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_video_chats":{"property":"canManageVideoChats","tgPropName":"can_manage_video_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_restrict_members":{"property":"canRestrictMembers","tgPropName":"can_restrict_members","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_promote_members":{"property":"canPromoteMembers","tgPropName":"can_promote_members","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_change_info":{"property":"canChangeInfo","tgPropName":"can_change_info","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_invite_users":{"property":"canInviteUsers","tgPropName":"can_invite_users","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_post_stories":{"property":"canPostStories","tgPropName":"can_post_stories","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_edit_stories":{"property":"canEditStories","tgPropName":"can_edit_stories","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_delete_stories":{"property":"canDeleteStories","tgPropName":"can_delete_stories","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_post_messages":{"property":"canPostMessages","tgPropName":"can_post_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_edit_messages":{"property":"canEditMessages","tgPropName":"can_edit_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_pin_messages":{"property":"canPinMessages","tgPropName":"can_pin_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_topics":{"property":"canManageTopics","tgPropName":"can_manage_topics","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_direct_messages":{"property":"canManageDirectMessages","tgPropName":"can_manage_direct_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_tags":{"property":"canManageTags","tgPropName":"can_manage_tags","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
