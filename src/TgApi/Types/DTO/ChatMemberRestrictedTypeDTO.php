<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that is under certain restrictions in the chat. Supergroups only.')]
#[See('https://core.telegram.org/bots/api#chatmemberrestricted')]
class ChatMemberRestrictedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Information about the user')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user,
        #[Description('_True_, if the user is a member of the chat at the moment of the request')]
        public bool $isMember,
        #[Description('_True_, if the user is allowed to send text messages, contacts, giveaways, giveaway winners, invoices, locations and venues')]
        public bool $canSendMessages,
        #[Description('_True_, if the user is allowed to send audios')]
        public bool $canSendAudios,
        #[Description('_True_, if the user is allowed to send documents')]
        public bool $canSendDocuments,
        #[Description('_True_, if the user is allowed to send photos')]
        public bool $canSendPhotos,
        #[Description('_True_, if the user is allowed to send videos')]
        public bool $canSendVideos,
        #[Description('_True_, if the user is allowed to send video notes')]
        public bool $canSendVideoNotes,
        #[Description('_True_, if the user is allowed to send voice notes')]
        public bool $canSendVoiceNotes,
        #[Description('_True_, if the user is allowed to send polls and checklists')]
        public bool $canSendPolls,
        #[Description('_True_, if the user is allowed to send animations, games, stickers and use inline bots')]
        public bool $canSendOtherMessages,
        #[Description('_True_, if the user is allowed to add web page previews to their messages')]
        public bool $canAddWebPagePreviews,
        #[Description('_True_, if the user is allowed to edit their own tag')]
        public bool $canEditTag,
        #[Description('_True_, if the user is allowed to change the chat title, photo and other settings')]
        public bool $canChangeInfo,
        #[Description('_True_, if the user is allowed to invite new users to the chat')]
        public bool $canInviteUsers,
        #[Description('_True_, if the user is allowed to pin messages')]
        public bool $canPinMessages,
        #[Description('_True_, if the user is allowed to create forum topics')]
        public bool $canManageTopics,
        #[Description('Date when restrictions will be lifted for this user; Unix time. If 0, then the user is restricted forever')]
        public int $untilDate,
        #[Description('The member"s status in the chat, always “restricted”')]
        public string $status = 'restricted',
        #[Description('Tag of the member')]
        public ?string $tag = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatMemberRestricted;
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
{"status":{"property":"status","tgPropName":"status","types":["string"],"tgTypes":[{"type":"str","literal":"restricted"}],"nullable":false,"required":true},"tag":{"property":"tag","tgPropName":"tag","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"is_member":{"property":"isMember","tgPropName":"is_member","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_messages":{"property":"canSendMessages","tgPropName":"can_send_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_audios":{"property":"canSendAudios","tgPropName":"can_send_audios","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_documents":{"property":"canSendDocuments","tgPropName":"can_send_documents","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_photos":{"property":"canSendPhotos","tgPropName":"can_send_photos","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_videos":{"property":"canSendVideos","tgPropName":"can_send_videos","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_video_notes":{"property":"canSendVideoNotes","tgPropName":"can_send_video_notes","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_voice_notes":{"property":"canSendVoiceNotes","tgPropName":"can_send_voice_notes","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_polls":{"property":"canSendPolls","tgPropName":"can_send_polls","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_send_other_messages":{"property":"canSendOtherMessages","tgPropName":"can_send_other_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_add_web_page_previews":{"property":"canAddWebPagePreviews","tgPropName":"can_add_web_page_previews","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_edit_tag":{"property":"canEditTag","tgPropName":"can_edit_tag","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_change_info":{"property":"canChangeInfo","tgPropName":"can_change_info","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_invite_users":{"property":"canInviteUsers","tgPropName":"can_invite_users","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_pin_messages":{"property":"canPinMessages","tgPropName":"can_pin_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"can_manage_topics":{"property":"canManageTopics","tgPropName":"can_manage_topics","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"until_date":{"property":"untilDate","tgPropName":"until_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
