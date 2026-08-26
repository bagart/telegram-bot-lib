<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This [object](https://core.telegram.org/bots/api#available-types) represents an incoming update.; ; At most **one** of the optional parameters can be present in any given update.')]
#[See('https://core.telegram.org/bots/api#update')]
class UpdateTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The update"s unique identifier. Update identifiers start from a certain positive number and increase sequentially. This identifier becomes especially handy if you"re using [webhooks](https://core.telegram.org/bots/api#setwebhook), since it allows you to ignore repeated updates or to restore the correct update sequence, should they get out of order. If there are no new updates for at least a week, then identifier of the next update will be chosen randomly instead of sequentially.')]
        public int $updateId,
        #[Description('New incoming message of any kind - text, photo, sticker, etc.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $message = null,
        #[Description('New version of a message that is known to the bot and was edited. This update may at times be triggered by changes to message fields that are either unavailable or not actively used by your bot.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $editedMessage = null,
        #[Description('New incoming channel post of any kind - text, photo, sticker, etc.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $channelPost = null,
        #[Description('New version of a channel post that is known to the bot and was edited. This update may at times be triggered by changes to message fields that are either unavailable or not actively used by your bot.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $editedChannelPost = null,
        #[Description('The bot was connected to or disconnected from a business account, or a user edited an existing connection with the bot')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BusinessConnectionTypeDTO $businessConnection = null,
        #[Description('New message from a connected business account')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $businessMessage = null,
        #[Description('New version of a message from a connected business account')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO $editedBusinessMessage = null,
        #[Description('Messages were deleted from a connected business account')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BusinessMessagesDeletedTypeDTO $deletedBusinessMessages = null,
        #[Description('A reaction to a message was changed by a user. The bot must be an administrator in the chat and must explicitly specify `"message_reaction"` in the list of _allowed\_updates_ to receive these updates. The update isn"t received for reactions set by bots.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageReactionUpdatedTypeDTO $messageReaction = null,
        #[Description('Reactions to a message with anonymous reactions were changed. The bot must be an administrator in the chat and must explicitly specify `"message_reaction_count"` in the list of _allowed\_updates_ to receive these updates. The updates are grouped and can be sent with delay up to a few minutes.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MessageReactionCountUpdatedTypeDTO $messageReactionCount = null,
        #[Description('New incoming [inline](https://core.telegram.org/bots/api#inline-mode) query')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryTypeDTO $inlineQuery = null,
        #[Description('The result of an [inline](https://core.telegram.org/bots/api#inline-mode) query that was chosen by a user and sent to their chat partner. Please see our documentation on the [feedback collecting](https://core.telegram.org/bots/inline#collecting-feedback) for details on how to enable these updates for your bot.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChosenInlineResultTypeDTO $chosenInlineResult = null,
        #[Description('New incoming callback query')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\CallbackQueryTypeDTO $callbackQuery = null,
        #[Description('New incoming shipping query. Only for invoices with flexible price')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ShippingQueryTypeDTO $shippingQuery = null,
        #[Description('New incoming pre-checkout query. Contains full information about checkout')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PreCheckoutQueryTypeDTO $preCheckoutQuery = null,
        #[Description('A user purchased paid media with a non-empty payload sent by the bot in a non-channel chat')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaPurchasedTypeDTO $purchasedPaidMedia = null,
        #[Description('New poll state. Bots receive only updates about manually stopped polls and polls, which are sent by the bot')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PollTypeDTO $poll = null,
        #[Description('A user changed their answer in a non-anonymous poll. Bots receive new votes only in polls that were sent by the bot itself.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\PollAnswerTypeDTO $pollAnswer = null,
        #[Description('The bot"s chat member status was updated in a chat. For private chats, this update is received only when the bot is blocked or unblocked by the user.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberUpdatedTypeDTO $myChatMember = null,
        #[Description('A chat member"s status was updated in a chat. The bot must be an administrator in the chat and must explicitly specify `"chat_member"` in the list of _allowed\_updates_ to receive these updates.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberUpdatedTypeDTO $chatMember = null,
        #[Description('A request to join the chat has been sent. The bot must have the _can\_invite\_users_ administrator right in the chat to receive these updates.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatJoinRequestTypeDTO $chatJoinRequest = null,
        #[Description('A chat boost was added or changed. The bot must be an administrator in the chat to receive these updates.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostUpdatedTypeDTO $chatBoost = null,
        #[Description('A boost was removed from a chat. The bot must be an administrator in the chat to receive these updates.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostRemovedTypeDTO $removedChatBoost = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Update;
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
{"update_id":{"property":"updateId","tgPropName":"update_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"message":{"property":"message","tgPropName":"message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"edited_message":{"property":"editedMessage","tgPropName":"edited_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"channel_post":{"property":"channelPost","tgPropName":"channel_post","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"edited_channel_post":{"property":"editedChannelPost","tgPropName":"edited_channel_post","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"business_connection":{"property":"businessConnection","tgPropName":"business_connection","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessConnectionTypeDTO"],"tgTypes":[{"type":"api-type","name":"BusinessConnection"}],"nullable":true,"required":false},"business_message":{"property":"businessMessage","tgPropName":"business_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"edited_business_message":{"property":"editedBusinessMessage","tgPropName":"edited_business_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"deleted_business_messages":{"property":"deletedBusinessMessages","tgPropName":"deleted_business_messages","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessMessagesDeletedTypeDTO"],"tgTypes":[{"type":"api-type","name":"BusinessMessagesDeleted"}],"nullable":true,"required":false},"message_reaction":{"property":"messageReaction","tgPropName":"message_reaction","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageReactionUpdatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"MessageReactionUpdated"}],"nullable":true,"required":false},"message_reaction_count":{"property":"messageReactionCount","tgPropName":"message_reaction_count","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageReactionCountUpdatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"MessageReactionCountUpdated"}],"nullable":true,"required":false},"inline_query":{"property":"inlineQuery","tgPropName":"inline_query","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineQueryTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineQuery"}],"nullable":true,"required":false},"chosen_inline_result":{"property":"chosenInlineResult","tgPropName":"chosen_inline_result","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChosenInlineResultTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChosenInlineResult"}],"nullable":true,"required":false},"callback_query":{"property":"callbackQuery","tgPropName":"callback_query","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\CallbackQueryTypeDTO"],"tgTypes":[{"type":"api-type","name":"CallbackQuery"}],"nullable":true,"required":false},"shipping_query":{"property":"shippingQuery","tgPropName":"shipping_query","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ShippingQueryTypeDTO"],"tgTypes":[{"type":"api-type","name":"ShippingQuery"}],"nullable":true,"required":false},"pre_checkout_query":{"property":"preCheckoutQuery","tgPropName":"pre_checkout_query","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PreCheckoutQueryTypeDTO"],"tgTypes":[{"type":"api-type","name":"PreCheckoutQuery"}],"nullable":true,"required":false},"purchased_paid_media":{"property":"purchasedPaidMedia","tgPropName":"purchased_paid_media","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PaidMediaPurchasedTypeDTO"],"tgTypes":[{"type":"api-type","name":"PaidMediaPurchased"}],"nullable":true,"required":false},"poll":{"property":"poll","tgPropName":"poll","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PollTypeDTO"],"tgTypes":[{"type":"api-type","name":"Poll"}],"nullable":true,"required":false},"poll_answer":{"property":"pollAnswer","tgPropName":"poll_answer","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PollAnswerTypeDTO"],"tgTypes":[{"type":"api-type","name":"PollAnswer"}],"nullable":true,"required":false},"my_chat_member":{"property":"myChatMember","tgPropName":"my_chat_member","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatMemberUpdatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatMemberUpdated"}],"nullable":true,"required":false},"chat_member":{"property":"chatMember","tgPropName":"chat_member","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatMemberUpdatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatMemberUpdated"}],"nullable":true,"required":false},"chat_join_request":{"property":"chatJoinRequest","tgPropName":"chat_join_request","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatJoinRequestTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatJoinRequest"}],"nullable":true,"required":false},"chat_boost":{"property":"chatBoost","tgPropName":"chat_boost","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatBoostUpdatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatBoostUpdated"}],"nullable":true,"required":false},"removed_chat_boost":{"property":"removedChatBoost","tgPropName":"removed_chat_boost","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatBoostRemovedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatBoostRemoved"}],"nullable":true,"required":false}}
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
