<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes actions that a non-administrator user is allowed to take in a chat.')]
#[See('https://core.telegram.org/bots/api#chatpermissions')]
class ChatPermissionsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('_True_, if the user is allowed to send text messages, contacts, giveaways, giveaway winners, invoices, locations and venues')]
        public ?bool $canSendMessages = null,
        #[Description('_True_, if the user is allowed to send audios')]
        public ?bool $canSendAudios = null,
        #[Description('_True_, if the user is allowed to send documents')]
        public ?bool $canSendDocuments = null,
        #[Description('_True_, if the user is allowed to send photos')]
        public ?bool $canSendPhotos = null,
        #[Description('_True_, if the user is allowed to send videos')]
        public ?bool $canSendVideos = null,
        #[Description('_True_, if the user is allowed to send video notes')]
        public ?bool $canSendVideoNotes = null,
        #[Description('_True_, if the user is allowed to send voice notes')]
        public ?bool $canSendVoiceNotes = null,
        #[Description('_True_, if the user is allowed to send polls and checklists')]
        public ?bool $canSendPolls = null,
        #[Description('_True_, if the user is allowed to send animations, games, stickers and use inline bots')]
        public ?bool $canSendOtherMessages = null,
        #[Description('_True_, if the user is allowed to add web page previews to their messages')]
        public ?bool $canAddWebPagePreviews = null,
        #[Description('_True_, if the user is allowed to edit their own tag')]
        public ?bool $canEditTag = null,
        #[Description('_True_, if the user is allowed to change the chat title, photo and other settings. Ignored in public supergroups')]
        public ?bool $canChangeInfo = null,
        #[Description('_True_, if the user is allowed to invite new users to the chat')]
        public ?bool $canInviteUsers = null,
        #[Description('_True_, if the user is allowed to pin messages. Ignored in public supergroups')]
        public ?bool $canPinMessages = null,
        #[Description('_True_, if the user is allowed to create forum topics. If omitted defaults to the value of can\_pin\_messages')]
        public ?bool $canManageTopics = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatPermissions;
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
{"can_send_messages":{"property":"canSendMessages","tgPropName":"can_send_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_audios":{"property":"canSendAudios","tgPropName":"can_send_audios","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_documents":{"property":"canSendDocuments","tgPropName":"can_send_documents","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_photos":{"property":"canSendPhotos","tgPropName":"can_send_photos","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_videos":{"property":"canSendVideos","tgPropName":"can_send_videos","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_video_notes":{"property":"canSendVideoNotes","tgPropName":"can_send_video_notes","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_voice_notes":{"property":"canSendVoiceNotes","tgPropName":"can_send_voice_notes","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_polls":{"property":"canSendPolls","tgPropName":"can_send_polls","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_send_other_messages":{"property":"canSendOtherMessages","tgPropName":"can_send_other_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_add_web_page_previews":{"property":"canAddWebPagePreviews","tgPropName":"can_add_web_page_previews","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_edit_tag":{"property":"canEditTag","tgPropName":"can_edit_tag","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_change_info":{"property":"canChangeInfo","tgPropName":"can_change_info","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_invite_users":{"property":"canInviteUsers","tgPropName":"can_invite_users","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_pin_messages":{"property":"canPinMessages","tgPropName":"can_pin_messages","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"can_manage_topics":{"property":"canManageTopics","tgPropName":"can_manage_topics","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
