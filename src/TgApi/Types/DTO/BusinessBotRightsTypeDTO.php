<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents the rights of a business bot.')]
#[See('https://core.telegram.org/bots/api#businessbotrights')]
class BusinessBotRightsTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('_True_, if the bot can send and edit messages in the private chats that had incoming messages in the last 24 hours')]
        public ?bool $canReply = true,
        #[Description('_True_, if the bot can mark incoming private messages as read')]
        public ?bool $canReadMessages = true,
        #[Description('_True_, if the bot can delete messages sent by the bot')]
        public ?bool $canDeleteSentMessages = true,
        #[Description('_True_, if the bot can delete all private messages in managed chats')]
        public ?bool $canDeleteAllMessages = true,
        #[Description('_True_, if the bot can edit the first and last name of the business account')]
        public ?bool $canEditName = true,
        #[Description('_True_, if the bot can edit the bio of the business account')]
        public ?bool $canEditBio = true,
        #[Description('_True_, if the bot can edit the profile photo of the business account')]
        public ?bool $canEditProfilePhoto = true,
        #[Description('_True_, if the bot can edit the username of the business account')]
        public ?bool $canEditUsername = true,
        #[Description('_True_, if the bot can change the privacy settings pertaining to gifts for the business account')]
        public ?bool $canChangeGiftSettings = true,
        #[Description('_True_, if the bot can view gifts and the amount of Telegram Stars owned by the business account')]
        public ?bool $canViewGiftsAndStars = true,
        #[Description('_True_, if the bot can convert regular gifts owned by the business account to Telegram Stars')]
        public ?bool $canConvertGiftsToStars = true,
        #[Description('_True_, if the bot can transfer and upgrade gifts owned by the business account')]
        public ?bool $canTransferAndUpgradeGifts = true,
        #[Description('_True_, if the bot can transfer Telegram Stars received by the business account to its own account, or use them to upgrade and transfer gifts')]
        public ?bool $canTransferStars = true,
        #[Description('_True_, if the bot can post, edit and delete stories on behalf of the business account')]
        public ?bool $canManageStories = true,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BusinessBotRights;
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
{"can_reply":{"property":"canReply","tgPropName":"can_reply","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_read_messages":{"property":"canReadMessages","tgPropName":"can_read_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_delete_sent_messages":{"property":"canDeleteSentMessages","tgPropName":"can_delete_sent_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_delete_all_messages":{"property":"canDeleteAllMessages","tgPropName":"can_delete_all_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_edit_name":{"property":"canEditName","tgPropName":"can_edit_name","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_edit_bio":{"property":"canEditBio","tgPropName":"can_edit_bio","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_edit_profile_photo":{"property":"canEditProfilePhoto","tgPropName":"can_edit_profile_photo","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_edit_username":{"property":"canEditUsername","tgPropName":"can_edit_username","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_change_gift_settings":{"property":"canChangeGiftSettings","tgPropName":"can_change_gift_settings","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_view_gifts_and_stars":{"property":"canViewGiftsAndStars","tgPropName":"can_view_gifts_and_stars","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_convert_gifts_to_stars":{"property":"canConvertGiftsToStars","tgPropName":"can_convert_gifts_to_stars","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_transfer_and_upgrade_gifts":{"property":"canTransferAndUpgradeGifts","tgPropName":"can_transfer_and_upgrade_gifts","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_transfer_stars":{"property":"canTransferStars","tgPropName":"can_transfer_stars","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"can_manage_stories":{"property":"canManageStories","tgPropName":"can_manage_stories","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false}}
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
