<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains full information about a chat.')]
#[See('https://core.telegram.org/bots/api#chatfullinfo')]
class ChatFullInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this chat.')]
        public string $id,
        #[Description('Type of the chat, can be either “private”, “group”, “supergroup” or “channel”')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\ChatFullInfoPropTypeEnum $type,
        #[Description('Identifier of the accent color for the chat name and backgrounds of the chat photo, reply header, and link preview. See [accent colors](https://core.telegram.org/bots/api#accent-colors) for more details.')]
        public int $accentColorId,
        #[Description('The maximum number of reactions that can be set on a message in the chat')]
        public int $maxReactionCount,
        #[Description('Information about types of gifts that are accepted by the chat or by the corresponding user for private chats')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\AcceptedGiftTypesTypeDTO $acceptedGiftTypes,
        #[Description('Title, for supergroups, channels and group chats')]
        public ?string $title = null,
        #[Description('Username, for private chats, supergroups and channels if available')]
        public ?string $username = null,
        #[Description('First name of the other party in a private chat')]
        public ?string $firstName = null,
        #[Description('Last name of the other party in a private chat')]
        public ?string $lastName = null,
        #[Description('_True_, if the supergroup chat is a forum (has [topics](https://telegram.org/blog/topics-in-groups-collectible-usernames#topics-in-groups) enabled)')]
        public ?bool $isForum = true,
        #[Description('_True_, if the chat is the direct messages chat of a channel')]
        public ?bool $isDirectMessages = true,
        #[Description('Chat photo')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatPhotoTypeDTO $photo = null,
        #[Description('If non-empty, the list of all [active chat usernames](https://telegram.org/blog/topics-in-groups-collectible-usernames#collectible-usernames); for private chats, supergroups and channels')]
        public ?array $activeUsernames = null,
        #[Description('For private chats, the date of birth of the user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BirthdateTypeDTO $birthdate = null,
        #[Description('For private chats with business accounts, the intro of the business')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BusinessIntroTypeDTO $businessIntro = null,
        #[Description('For private chats with business accounts, the location of the business')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BusinessLocationTypeDTO $businessLocation = null,
        #[Description('For private chats with business accounts, the opening hours of the business')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BusinessOpeningHoursTypeDTO $businessOpeningHours = null,
        #[Description('For private chats, the personal channel of the user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $personalChat = null,
        #[Description('Information about the corresponding channel chat; for direct messages chats only')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $parentChat = null,
        #[Description('List of available reactions allowed in the chat. If omitted, then all [emoji reactions](https://core.telegram.org/bots/api#reactiontypeemoji) are allowed.')]
        public ?array $availableReactions = null,
        #[Description('Custom emoji identifier of the emoji chosen by the chat for the reply header and link preview background')]
        public ?string $backgroundCustomEmojiId = null,
        #[Description('Identifier of the accent color for the chat"s profile background. See [profile accent colors](https://core.telegram.org/bots/api#profile-accent-colors) for more details.')]
        public ?int $profileAccentColorId = null,
        #[Description('Custom emoji identifier of the emoji chosen by the chat for its profile background')]
        public ?string $profileBackgroundCustomEmojiId = null,
        #[Description('Custom emoji identifier of the emoji status of the chat or the other party in a private chat')]
        public ?string $emojiStatusCustomEmojiId = null,
        #[Description('Expiration date of the emoji status of the chat or the other party in a private chat, in Unix time, if any')]
        public ?int $emojiStatusExpirationDate = null,
        #[Description('Bio of the other party in a private chat')]
        public ?string $bio = null,
        #[Description('_True_, if privacy settings of the other party in the private chat allows to use `tg://user?id=<user_id>` links only in chats with the user')]
        public ?bool $hasPrivateForwards = true,
        #[Description('_True_, if the privacy settings of the other party restrict sending voice and video note messages in the private chat')]
        public ?bool $hasRestrictedVoiceAndVideoMessages = true,
        #[Description('_True_, if users need to join the supergroup before they can send messages')]
        public ?bool $joinToSendMessages = true,
        #[Description('_True_, if all users directly joining the supergroup without using an invite link need to be approved by supergroup administrators')]
        public ?bool $joinByRequest = true,
        #[Description('Description, for groups, supergroups and channel chats')]
        public ?string $description = null,
        #[Description('Primary invite link, for groups, supergroups and channel chats')]
        public ?string $inviteLink = null,
        #[Description('The most recent pinned message (by sending date)')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $pinnedMessage = null,
        #[Description('Default chat member permissions, for groups and supergroups')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatPermissionsTypeDTO $permissions = null,
        #[Description('_True_, if paid media messages can be sent or forwarded to the channel chat. The field is available only for channel chats.')]
        public ?bool $canSendPaidMedia = true,
        #[Description('For supergroups, the minimum allowed delay between consecutive messages sent by each unprivileged user; in seconds')]
        public ?int $slowModeDelay = null,
        #[Description('For supergroups, the minimum number of boosts that a non-administrator user needs to add in order to ignore slow mode and chat permissions')]
        public ?int $unrestrictBoostCount = null,
        #[Description('The time after which all messages sent to the chat will be automatically deleted; in seconds')]
        public ?int $messageAutoDeleteTime = null,
        #[Description('_True_, if aggressive anti-spam checks are enabled in the supergroup. The field is only available to chat administrators.')]
        public ?bool $hasAggressiveAntiSpamEnabled = true,
        #[Description('_True_, if non-administrators can only get the list of bots and administrators in the chat')]
        public ?bool $hasHiddenMembers = true,
        #[Description('_True_, if messages from the chat can"t be forwarded to other chats')]
        public ?bool $hasProtectedContent = true,
        #[Description('_True_, if new chat members will have access to old messages; available only to chat administrators')]
        public ?bool $hasVisibleHistory = true,
        #[Description('For supergroups, name of the group sticker set')]
        public ?string $stickerSetName = null,
        #[Description('_True_, if the bot can change the group sticker set')]
        public ?bool $canSetStickerSet = true,
        #[Description('For supergroups, the name of the group"s custom emoji sticker set. Custom emoji from this set can be used by all users and bots in the group.')]
        public ?string $customEmojiStickerSetName = null,
        #[Description('Unique identifier for the linked chat, i.e. the discussion group identifier for a channel and vice versa; for supergroups and channel chats.')]
        public ?string $linkedChatId = null,
        #[Description('For supergroups, the location to which the supergroup is connected')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatLocationTypeDTO $location = null,
        #[Description('For private chats, the rating of the user if any')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserRatingTypeDTO $rating = null,
        #[Description('For private chats, the first audio added to the profile of the user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\AudioTypeDTO $firstProfileAudio = null,
        #[Description('The color scheme based on a unique gift that must be used for the chat"s name, message replies and link previews')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftColorsTypeDTO $uniqueGiftColors = null,
        #[Description('The number of Telegram Stars a general user have to pay to send a message to the chat')]
        public ?int $paidMessageStarCount = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ChatFullInfo;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"type":{"property":"type","tgPropName":"type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ChatFullInfoPropTypeEnum"],"tgTypes":[{"type":"str","literal":"private"},{"type":"str","literal":"group"},{"type":"str","literal":"supergroup"},{"type":"str","literal":"channel"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"username":{"property":"username","tgPropName":"username","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"first_name":{"property":"firstName","tgPropName":"first_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"last_name":{"property":"lastName","tgPropName":"last_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"is_forum":{"property":"isForum","tgPropName":"is_forum","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_direct_messages":{"property":"isDirectMessages","tgPropName":"is_direct_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"accent_color_id":{"property":"accentColorId","tgPropName":"accent_color_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"max_reaction_count":{"property":"maxReactionCount","tgPropName":"max_reaction_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"photo":{"property":"photo","tgPropName":"photo","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatPhotoTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatPhoto"}],"nullable":true,"required":false},"active_usernames":{"property":"activeUsernames","tgPropName":"active_usernames","types":[["string"]],"tgTypes":[{"type":"array","of":{"type":"str"}}],"nullable":true,"required":false},"birthdate":{"property":"birthdate","tgPropName":"birthdate","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BirthdateTypeDTO"],"tgTypes":[{"type":"api-type","name":"Birthdate"}],"nullable":true,"required":false},"business_intro":{"property":"businessIntro","tgPropName":"business_intro","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessIntroTypeDTO"],"tgTypes":[{"type":"api-type","name":"BusinessIntro"}],"nullable":true,"required":false},"business_location":{"property":"businessLocation","tgPropName":"business_location","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessLocationTypeDTO"],"tgTypes":[{"type":"api-type","name":"BusinessLocation"}],"nullable":true,"required":false},"business_opening_hours":{"property":"businessOpeningHours","tgPropName":"business_opening_hours","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessOpeningHoursTypeDTO"],"tgTypes":[{"type":"api-type","name":"BusinessOpeningHours"}],"nullable":true,"required":false},"personal_chat":{"property":"personalChat","tgPropName":"personal_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"parent_chat":{"property":"parentChat","tgPropName":"parent_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"available_reactions":{"property":"availableReactions","tgPropName":"available_reactions","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReactionTypeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"ReactionType"}}],"nullable":true,"required":false},"background_custom_emoji_id":{"property":"backgroundCustomEmojiId","tgPropName":"background_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"profile_accent_color_id":{"property":"profileAccentColorId","tgPropName":"profile_accent_color_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"profile_background_custom_emoji_id":{"property":"profileBackgroundCustomEmojiId","tgPropName":"profile_background_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"emoji_status_custom_emoji_id":{"property":"emojiStatusCustomEmojiId","tgPropName":"emoji_status_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"emoji_status_expiration_date":{"property":"emojiStatusExpirationDate","tgPropName":"emoji_status_expiration_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"bio":{"property":"bio","tgPropName":"bio","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"has_private_forwards":{"property":"hasPrivateForwards","tgPropName":"has_private_forwards","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_restricted_voice_and_video_messages":{"property":"hasRestrictedVoiceAndVideoMessages","tgPropName":"has_restricted_voice_and_video_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"join_to_send_messages":{"property":"joinToSendMessages","tgPropName":"join_to_send_messages","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"join_by_request":{"property":"joinByRequest","tgPropName":"join_by_request","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"invite_link":{"property":"inviteLink","tgPropName":"invite_link","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"pinned_message":{"property":"pinnedMessage","tgPropName":"pinned_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"permissions":{"property":"permissions","tgPropName":"permissions","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatPermissionsTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatPermissions"}],"nullable":true,"required":false},"accepted_gift_types":{"property":"acceptedGiftTypes","tgPropName":"accepted_gift_types","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AcceptedGiftTypesTypeDTO"],"tgTypes":[{"type":"api-type","name":"AcceptedGiftTypes"}],"nullable":false,"required":true},"can_send_paid_media":{"property":"canSendPaidMedia","tgPropName":"can_send_paid_media","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"slow_mode_delay":{"property":"slowModeDelay","tgPropName":"slow_mode_delay","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"unrestrict_boost_count":{"property":"unrestrictBoostCount","tgPropName":"unrestrict_boost_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"message_auto_delete_time":{"property":"messageAutoDeleteTime","tgPropName":"message_auto_delete_time","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"has_aggressive_anti_spam_enabled":{"property":"hasAggressiveAntiSpamEnabled","tgPropName":"has_aggressive_anti_spam_enabled","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_hidden_members":{"property":"hasHiddenMembers","tgPropName":"has_hidden_members","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_protected_content":{"property":"hasProtectedContent","tgPropName":"has_protected_content","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_visible_history":{"property":"hasVisibleHistory","tgPropName":"has_visible_history","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"sticker_set_name":{"property":"stickerSetName","tgPropName":"sticker_set_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"can_set_sticker_set":{"property":"canSetStickerSet","tgPropName":"can_set_sticker_set","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"custom_emoji_sticker_set_name":{"property":"customEmojiStickerSetName","tgPropName":"custom_emoji_sticker_set_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"linked_chat_id":{"property":"linkedChatId","tgPropName":"linked_chat_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":true,"required":false},"location":{"property":"location","tgPropName":"location","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatLocationTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatLocation"}],"nullable":true,"required":false},"rating":{"property":"rating","tgPropName":"rating","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserRatingTypeDTO"],"tgTypes":[{"type":"api-type","name":"UserRating"}],"nullable":true,"required":false},"first_profile_audio":{"property":"firstProfileAudio","tgPropName":"first_profile_audio","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AudioTypeDTO"],"tgTypes":[{"type":"api-type","name":"Audio"}],"nullable":true,"required":false},"unique_gift_colors":{"property":"uniqueGiftColors","tgPropName":"unique_gift_colors","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftColorsTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftColors"}],"nullable":true,"required":false},"paid_message_star_count":{"property":"paidMessageStarCount","tgPropName":"paid_message_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
