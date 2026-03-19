<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that has some additional privileges.')]
#[See('https://core.telegram.org/bots/api#chatmemberadministrator')]
class ChatMemberAdministratorTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Information about the user')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user,
        #[Description('_True_, if the bot is allowed to edit administrator privileges of that user')]
        public bool $canBeEdited,
        #[Description('_True_, if the user"s presence in the chat is hidden')]
        public bool $isAnonymous,
        #[Description('_True_, if the administrator can access the chat event log, get boost list, see hidden supergroup and channel members, report spam messages, ignore slow mode, and send messages to the chat without paying Telegram Stars. Implied by any other administrator privilege.')]
        public bool $canManageChat,
        #[Description('_True_, if the administrator can delete messages of other users')]
        public bool $canDeleteMessages,
        #[Description('_True_, if the administrator can manage video chats')]
        public bool $canManageVideoChats,
        #[Description('_True_, if the administrator can restrict, ban or unban chat members, or access supergroup statistics')]
        public bool $canRestrictMembers,
        #[Description('_True_, if the administrator can add new administrators with a subset of their own privileges or demote administrators that they have promoted, directly or indirectly (promoted by administrators that were appointed by the user)')]
        public bool $canPromoteMembers,
        #[Description('_True_, if the user is allowed to change the chat title, photo and other settings')]
        public bool $canChangeInfo,
        #[Description('_True_, if the user is allowed to invite new users to the chat')]
        public bool $canInviteUsers,
        #[Description('_True_, if the administrator can post stories to the chat')]
        public bool $canPostStories,
        #[Description('_True_, if the administrator can edit stories posted by other users, post stories to the chat page, pin chat stories, and access the chat"s story archive')]
        public bool $canEditStories,
        #[Description('_True_, if the administrator can delete stories posted by other users')]
        public bool $canDeleteStories,
        #[Description('The member"s status in the chat, always “administrator”')]
        public string $status = 'administrator',
        #[Description('_True_, if the administrator can post messages in the channel, approve suggested posts, or access channel statistics; for channels only')]
        public ?bool $canPostMessages = null,
        #[Description('_True_, if the administrator can edit messages of other users and can pin messages; for channels only')]
        public ?bool $canEditMessages = null,
        #[Description('_True_, if the user is allowed to pin messages; for groups and supergroups only')]
        public ?bool $canPinMessages = null,
        #[Description('_True_, if the user is allowed to create, rename, close, and reopen forum topics; for supergroups only')]
        public ?bool $canManageTopics = null,
        #[Description('_True_, if the administrator can manage direct messages of the channel and decline suggested posts; for channels only')]
        public ?bool $canManageDirectMessages = null,
        #[Description('_True_, if the administrator can edit the tags of regular members; for groups and supergroups only. If omitted defaults to the value of can\_pin\_messages.')]
        public ?bool $canManageTags = null,
        #[Description('Custom title for this user')]
        public ?string $customTitle = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatMemberAdministrator;
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
{"status":{"property":"status","tgPropName":"status","types":["string"],"tgTypes":[{"type":"str","literal":"administrator"}],"nullable":false,"required":true},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"can_be_edited":{"property":"canBeEdited","tgPropName":"can_be_edited","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"is_anonymous":{"property":"isAnonymous","tgPropName":"is_anonymous","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_manage_chat":{"property":"canManageChat","tgPropName":"can_manage_chat","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_delete_messages":{"property":"canDeleteMessages","tgPropName":"can_delete_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_manage_video_chats":{"property":"canManageVideoChats","tgPropName":"can_manage_video_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_restrict_members":{"property":"canRestrictMembers","tgPropName":"can_restrict_members","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_promote_members":{"property":"canPromoteMembers","tgPropName":"can_promote_members","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_change_info":{"property":"canChangeInfo","tgPropName":"can_change_info","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_invite_users":{"property":"canInviteUsers","tgPropName":"can_invite_users","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_post_stories":{"property":"canPostStories","tgPropName":"can_post_stories","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_edit_stories":{"property":"canEditStories","tgPropName":"can_edit_stories","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_delete_stories":{"property":"canDeleteStories","tgPropName":"can_delete_stories","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_post_messages":{"property":"canPostMessages","tgPropName":"can_post_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_edit_messages":{"property":"canEditMessages","tgPropName":"can_edit_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_pin_messages":{"property":"canPinMessages","tgPropName":"can_pin_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_topics":{"property":"canManageTopics","tgPropName":"can_manage_topics","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_direct_messages":{"property":"canManageDirectMessages","tgPropName":"can_manage_direct_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_tags":{"property":"canManageTags","tgPropName":"can_manage_tags","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"custom_title":{"property":"customTitle","tgPropName":"custom_title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
