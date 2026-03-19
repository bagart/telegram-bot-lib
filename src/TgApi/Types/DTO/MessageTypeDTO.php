<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a message.')]
#[See('https://core.telegram.org/bots/api#message')]
class MessageTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique message identifier inside this chat. In specific instances (e.g., message containing a video sent to a big chat), the server might automatically schedule a message instead of sending it immediately. In such cases, this field will be 0 and the relevant message will be unusable until it is actually sent')]
        public int $messageId,
        #[Description('Date the message was sent in Unix time. It is always a positive number, representing a valid date.')]
        public int $date,
        #[Description('Chat the message belongs to')]
        public ChatTypeDTO $chat,
        #[Description('Unique identifier of a message thread or forum topic to which the message belongs; for supergroups and private chats only')]
        public ?int $messageThreadId = null,
        #[Description('Information about the direct messages chat topic that contains the message')]
        public ?DirectMessagesTopicTypeDTO $directMessagesTopic = null,
        #[Description('Sender of the message; may be empty for messages sent to channels. For backward compatibility, if the message was sent on behalf of a chat, the field contains a fake sender user in non-channel chats')]
        public ?UserTypeDTO $from = null,
        #[Description('Sender of the message when sent on behalf of a chat. For example, the supergroup itself for messages sent by its anonymous administrators or a linked channel for messages automatically forwarded to the channel"s discussion group. For backward compatibility, if the message was sent on behalf of a chat, the field _from_ contains a fake sender user in non-channel chats.')]
        public ?ChatTypeDTO $senderChat = null,
        #[Description('If the sender of the message boosted the chat, the number of boosts added by the user')]
        public ?int $senderBoostCount = null,
        #[Description('The bot that actually sent the message on behalf of the business account. Available only for outgoing messages sent on behalf of the connected business account.')]
        public ?UserTypeDTO $senderBusinessBot = null,
        #[Description('Tag or custom title of the sender of the message; for supergroups only')]
        public ?string $senderTag = null,
        #[Description('Unique identifier of the business connection from which the message was received. If non-empty, the message belongs to a chat of the corresponding business account that is independent from any potential bot chat which might share the same identifier.')]
        public ?string $businessConnectionId = null,
        #[Description('Information about the original message for forwarded messages')]
        public ?MessageOriginTypeDTO $forwardOrigin = null,
        #[Description('_True_, if the message is sent to a topic in a forum supergroup or a private chat with the bot')]
        public ?bool $isTopicMessage = true,
        #[Description('_True_, if the message is a channel post that was automatically forwarded to the connected discussion group')]
        public ?bool $isAutomaticForward = true,
        #[Description('For replies in the same chat and message thread, the original message. Note that the [Message](https://core.telegram.org/bots/api#message) object in this field will not contain further _reply\_to\_message_ fields even if it itself is a reply.')]
        public ?MessageTypeDTO $replyToMessage = null,
        #[Description('Information about the message that is being replied to, which may come from another chat or forum topic')]
        public ?ExternalReplyInfoTypeDTO $externalReply = null,
        #[Description('For replies that quote part of the original message, the quoted part of the message')]
        public ?TextQuoteTypeDTO $quote = null,
        #[Description('For replies to a story, the original story')]
        public ?StoryTypeDTO $replyToStory = null,
        #[Description('Identifier of the specific checklist task that is being replied to')]
        public ?int $replyToChecklistTaskId = null,
        #[Description('Bot through which the message was sent')]
        public ?UserTypeDTO $viaBot = null,
        #[Description('Date the message was last edited in Unix time')]
        public ?int $editDate = null,
        #[Description('_True_, if the message can"t be forwarded')]
        public ?bool $hasProtectedContent = true,
        #[Description('_True_, if the message was sent by an implicit action, for example, as an away or a greeting business message, or as a scheduled message')]
        public ?bool $isFromOffline = true,
        #[Description('_True_, if the message is a paid post. Note that such posts must not be deleted for 24 hours to receive the payment and can"t be edited.')]
        public ?bool $isPaidPost = true,
        #[Description('The unique identifier inside this chat of a media message group this message belongs to')]
        public ?string $mediaGroupId = null,
        #[Description('Signature of the post author for messages in channels, or the custom title of an anonymous group administrator')]
        public ?string $authorSignature = null,
        #[Description('The number of Telegram Stars that were paid by the sender of the message to send it')]
        public ?int $paidStarCount = null,
        #[Description('For text messages, the actual UTF-8 text of the message')]
        public ?string $text = null,
        #[Description('For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text')]
        public ?array $entities = null,
        #[Description('Options used for link preview generation for the message, if it is a text message and link preview options were changed')]
        public ?LinkPreviewOptionsTypeDTO $linkPreviewOptions = null,
        #[Description('Information about suggested post parameters if the message is a suggested post in a channel direct messages chat. If the message is an approved or declined suggested post, then it can"t be edited.')]
        public ?SuggestedPostInfoTypeDTO $suggestedPostInfo = null,
        #[Description('Unique identifier of the message effect added to the message')]
        public ?string $effectId = null,
        #[Description('Message is an animation, information about the animation. For backward compatibility, when this field is set, the _document_ field will also be set')]
        public ?AnimationTypeDTO $animation = null,
        #[Description('Message is an audio file, information about the file')]
        public ?AudioTypeDTO $audio = null,
        #[Description('Message is a general file, information about the file')]
        public ?DocumentTypeDTO $document = null,
        #[Description('Message contains paid media; information about the paid media')]
        public ?PaidMediaInfoTypeDTO $paidMedia = null,
        #[Description('Message is a photo, available sizes of the photo')]
        public ?array $photo = null,
        #[Description('Message is a sticker, information about the sticker')]
        public ?StickerTypeDTO $sticker = null,
        #[Description('Message is a forwarded story')]
        public ?StoryTypeDTO $story = null,
        #[Description('Message is a video, information about the video')]
        public ?VideoTypeDTO $video = null,
        #[Description('Message is a [video note](https://telegram.org/blog/video-messages-and-telescope), information about the video message')]
        public ?VideoNoteTypeDTO $videoNote = null,
        #[Description('Message is a voice message, information about the file')]
        public ?VoiceTypeDTO $voice = null,
        #[Description('Caption for the animation, audio, document, paid media, photo, video or voice')]
        public ?string $caption = null,
        #[Description('For messages with a caption, special entities like usernames, URLs, bot commands, etc. that appear in the caption')]
        public ?array $captionEntities = null,
        #[Description('_True_, if the caption must be shown above the message media')]
        public ?bool $showCaptionAboveMedia = true,
        #[Description('_True_, if the message media is covered by a spoiler animation')]
        public ?bool $hasMediaSpoiler = true,
        #[Description('Message is a checklist')]
        public ?ChecklistTypeDTO $checklist = null,
        #[Description('Message is a shared contact, information about the contact')]
        public ?ContactTypeDTO $contact = null,
        #[Description('Message is a dice with random value')]
        public ?DiceTypeDTO $dice = null,
        #[Description('Message is a game, information about the game. [More about games »](https://core.telegram.org/bots/api#games)')]
        public ?GameTypeDTO $game = null,
        #[Description('Message is a native poll, information about the poll')]
        public ?PollTypeDTO $poll = null,
        #[Description('Message is a venue, information about the venue. For backward compatibility, when this field is set, the _location_ field will also be set')]
        public ?VenueTypeDTO $venue = null,
        #[Description('Message is a shared location, information about the location')]
        public ?LocationTypeDTO $location = null,
        #[Description('New members that were added to the group or supergroup and information about them (the bot itself may be one of these members)')]
        public ?array $newChatMembers = null,
        #[Description('A member was removed from the group, information about them (this member may be the bot itself)')]
        public ?UserTypeDTO $leftChatMember = null,
        #[Description('Service message: chat owner has left')]
        public ?ChatOwnerLeftTypeDTO $chatOwnerLeft = null,
        #[Description('Service message: chat owner has changed')]
        public ?ChatOwnerChangedTypeDTO $chatOwnerChanged = null,
        #[Description('A chat title was changed to this value')]
        public ?string $newChatTitle = null,
        #[Description('A chat photo was change to this value')]
        public ?array $newChatPhoto = null,
        #[Description('Service message: the chat photo was deleted')]
        public ?bool $deleteChatPhoto = true,
        #[Description('Service message: the group has been created')]
        public ?bool $groupChatCreated = true,
        #[Description('Service message: the supergroup has been created. This field can"t be received in a message coming through updates, because bot can"t be a member of a supergroup when it is created. It can only be found in reply\_to\_message if someone replies to a very first message in a directly created supergroup.')]
        public ?bool $supergroupChatCreated = true,
        #[Description('Service message: the channel has been created. This field can"t be received in a message coming through updates, because bot can"t be a member of a channel when it is created. It can only be found in reply\_to\_message if someone replies to a very first message in a channel.')]
        public ?bool $channelChatCreated = true,
        #[Description('Service message: auto-delete timer settings changed in the chat')]
        public ?MessageAutoDeleteTimerChangedTypeDTO $messageAutoDeleteTimerChanged = null,
        #[Description('The group has been migrated to a supergroup with the specified identifier.')]
        public ?string $migrateToChatId = null,
        #[Description('The supergroup has been migrated from a group with the specified identifier.')]
        public ?string $migrateFromChatId = null,
        #[Description('Specified message was pinned. Note that the [Message](https://core.telegram.org/bots/api#message) object in this field will not contain further _reply\_to\_message_ fields even if it itself is a reply.')]
        public ?MaybeInaccessibleMessageTypeDTO $pinnedMessage = null,
        #[Description('Message is an invoice for a [payment](https://core.telegram.org/bots/api#payments), information about the invoice. [More about payments »](https://core.telegram.org/bots/api#payments)')]
        public ?InvoiceTypeDTO $invoice = null,
        #[Description('Message is a service message about a successful payment, information about the payment. [More about payments »](https://core.telegram.org/bots/api#payments)')]
        public ?SuccessfulPaymentTypeDTO $successfulPayment = null,
        #[Description('Message is a service message about a refunded payment, information about the payment. [More about payments »](https://core.telegram.org/bots/api#payments)')]
        public ?RefundedPaymentTypeDTO $refundedPayment = null,
        #[Description('Service message: users were shared with the bot')]
        public ?UsersSharedTypeDTO $usersShared = null,
        #[Description('Service message: a chat was shared with the bot')]
        public ?ChatSharedTypeDTO $chatShared = null,
        #[Description('Service message: a regular gift was sent or received')]
        public ?GiftInfoTypeDTO $gift = null,
        #[Description('Service message: a unique gift was sent or received')]
        public ?UniqueGiftInfoTypeDTO $uniqueGift = null,
        #[Description('Service message: upgrade of a gift was purchased after the gift was sent')]
        public ?GiftInfoTypeDTO $giftUpgradeSent = null,
        #[Description('The domain name of the website on which the user has logged in. [More about Telegram Login »](https://core.telegram.org/widgets/login)')]
        public ?string $connectedWebsite = null,
        #[Description('Service message: the user allowed the bot to write messages after adding it to the attachment or side menu, launching a Web App from a link, or accepting an explicit request from a Web App sent by the method [requestWriteAccess](https://core.telegram.org/bots/webapps#initializing-mini-apps)')]
        public ?WriteAccessAllowedTypeDTO $writeAccessAllowed = null,
        #[Description('Telegram Passport data')]
        public ?PassportDataTypeDTO $passportData = null,
        #[Description('Service message. A user in the chat triggered another user"s proximity alert while sharing Live Location.')]
        public ?ProximityAlertTriggeredTypeDTO $proximityAlertTriggered = null,
        #[Description('Service message: user boosted the chat')]
        public ?ChatBoostAddedTypeDTO $boostAdded = null,
        #[Description('Service message: chat background set')]
        public ?ChatBackgroundTypeDTO $chatBackgroundSet = null,
        #[Description('Service message: some tasks in a checklist were marked as done or not done')]
        public ?ChecklistTasksDoneTypeDTO $checklistTasksDone = null,
        #[Description('Service message: tasks were added to a checklist')]
        public ?ChecklistTasksAddedTypeDTO $checklistTasksAdded = null,
        #[Description('Service message: the price for paid messages in the corresponding direct messages chat of a channel has changed')]
        public ?DirectMessagePriceChangedTypeDTO $directMessagePriceChanged = null,
        #[Description('Service message: forum topic created')]
        public ?ForumTopicCreatedTypeDTO $forumTopicCreated = null,
        #[Description('Service message: forum topic edited')]
        public ?ForumTopicEditedTypeDTO $forumTopicEdited = null,
        #[Description('Service message: forum topic closed')]
        public ?ForumTopicClosedTypeDTO $forumTopicClosed = null,
        #[Description('Service message: forum topic reopened')]
        public ?ForumTopicReopenedTypeDTO $forumTopicReopened = null,
        #[Description('Service message: the "General" forum topic hidden')]
        public ?GeneralForumTopicHiddenTypeDTO $generalForumTopicHidden = null,
        #[Description('Service message: the "General" forum topic unhidden')]
        public ?GeneralForumTopicUnhiddenTypeDTO $generalForumTopicUnhidden = null,
        #[Description('Service message: a scheduled giveaway was created')]
        public ?GiveawayCreatedTypeDTO $giveawayCreated = null,
        #[Description('The message is a scheduled giveaway message')]
        public ?GiveawayTypeDTO $giveaway = null,
        #[Description('A giveaway with public winners was completed')]
        public ?GiveawayWinnersTypeDTO $giveawayWinners = null,
        #[Description('Service message: a giveaway without public winners was completed')]
        public ?GiveawayCompletedTypeDTO $giveawayCompleted = null,
        #[Description('Service message: the price for paid messages has changed in the chat')]
        public ?PaidMessagePriceChangedTypeDTO $paidMessagePriceChanged = null,
        #[Description('Service message: a suggested post was approved')]
        public ?SuggestedPostApprovedTypeDTO $suggestedPostApproved = null,
        #[Description('Service message: approval of a suggested post has failed')]
        public ?SuggestedPostApprovalFailedTypeDTO $suggestedPostApprovalFailed = null,
        #[Description('Service message: a suggested post was declined')]
        public ?SuggestedPostDeclinedTypeDTO $suggestedPostDeclined = null,
        #[Description('Service message: payment for a suggested post was received')]
        public ?SuggestedPostPaidTypeDTO $suggestedPostPaid = null,
        #[Description('Service message: payment for a suggested post was refunded')]
        public ?SuggestedPostRefundedTypeDTO $suggestedPostRefunded = null,
        #[Description('Service message: video chat scheduled')]
        public ?VideoChatScheduledTypeDTO $videoChatScheduled = null,
        #[Description('Service message: video chat started')]
        public ?VideoChatStartedTypeDTO $videoChatStarted = null,
        #[Description('Service message: video chat ended')]
        public ?VideoChatEndedTypeDTO $videoChatEnded = null,
        #[Description('Service message: new participants invited to a video chat')]
        public ?VideoChatParticipantsInvitedTypeDTO $videoChatParticipantsInvited = null,
        #[Description('Service message: data sent by a Web App')]
        public ?WebAppDataTypeDTO $webAppData = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message. `login_url` buttons are represented as ordinary `url` buttons.')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::Message;
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
{"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"direct_messages_topic":{"property":"directMessagesTopic","tgPropName":"direct_messages_topic","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DirectMessagesTopicTypeDTO"],"tgTypes":[{"type":"api-type","name":"DirectMessagesTopic"}],"nullable":true,"required":false},"from":{"property":"from","tgPropName":"from","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"sender_chat":{"property":"senderChat","tgPropName":"sender_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"sender_boost_count":{"property":"senderBoostCount","tgPropName":"sender_boost_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"sender_business_bot":{"property":"senderBusinessBot","tgPropName":"sender_business_bot","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"sender_tag":{"property":"senderTag","tgPropName":"sender_tag","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat":{"property":"chat","tgPropName":"chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":false,"required":true},"forward_origin":{"property":"forwardOrigin","tgPropName":"forward_origin","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageOriginTypeDTO"],"tgTypes":[{"type":"api-type","name":"MessageOrigin"}],"nullable":true,"required":false},"is_topic_message":{"property":"isTopicMessage","tgPropName":"is_topic_message","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_automatic_forward":{"property":"isAutomaticForward","tgPropName":"is_automatic_forward","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"reply_to_message":{"property":"replyToMessage","tgPropName":"reply_to_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"external_reply":{"property":"externalReply","tgPropName":"external_reply","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ExternalReplyInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"ExternalReplyInfo"}],"nullable":true,"required":false},"quote":{"property":"quote","tgPropName":"quote","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\TextQuoteTypeDTO"],"tgTypes":[{"type":"api-type","name":"TextQuote"}],"nullable":true,"required":false},"reply_to_story":{"property":"replyToStory","tgPropName":"reply_to_story","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryTypeDTO"],"tgTypes":[{"type":"api-type","name":"Story"}],"nullable":true,"required":false},"reply_to_checklist_task_id":{"property":"replyToChecklistTaskId","tgPropName":"reply_to_checklist_task_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"via_bot":{"property":"viaBot","tgPropName":"via_bot","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"edit_date":{"property":"editDate","tgPropName":"edit_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"has_protected_content":{"property":"hasProtectedContent","tgPropName":"has_protected_content","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_from_offline":{"property":"isFromOffline","tgPropName":"is_from_offline","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_paid_post":{"property":"isPaidPost","tgPropName":"is_paid_post","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"media_group_id":{"property":"mediaGroupId","tgPropName":"media_group_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"author_signature":{"property":"authorSignature","tgPropName":"author_signature","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"paid_star_count":{"property":"paidStarCount","tgPropName":"paid_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"entities":{"property":"entities","tgPropName":"entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"link_preview_options":{"property":"linkPreviewOptions","tgPropName":"link_preview_options","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LinkPreviewOptionsTypeDTO"],"tgTypes":[{"type":"api-type","name":"LinkPreviewOptions"}],"nullable":true,"required":false},"suggested_post_info":{"property":"suggestedPostInfo","tgPropName":"suggested_post_info","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostInfo"}],"nullable":true,"required":false},"effect_id":{"property":"effectId","tgPropName":"effect_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"animation":{"property":"animation","tgPropName":"animation","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AnimationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Animation"}],"nullable":true,"required":false},"audio":{"property":"audio","tgPropName":"audio","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AudioTypeDTO"],"tgTypes":[{"type":"api-type","name":"Audio"}],"nullable":true,"required":false},"document":{"property":"document","tgPropName":"document","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DocumentTypeDTO"],"tgTypes":[{"type":"api-type","name":"Document"}],"nullable":true,"required":false},"paid_media":{"property":"paidMedia","tgPropName":"paid_media","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PaidMediaInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"PaidMediaInfo"}],"nullable":true,"required":false},"photo":{"property":"photo","tgPropName":"photo","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PhotoSize"}}],"nullable":true,"required":false},"sticker":{"property":"sticker","tgPropName":"sticker","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StickerTypeDTO"],"tgTypes":[{"type":"api-type","name":"Sticker"}],"nullable":true,"required":false},"story":{"property":"story","tgPropName":"story","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StoryTypeDTO"],"tgTypes":[{"type":"api-type","name":"Story"}],"nullable":true,"required":false},"video":{"property":"video","tgPropName":"video","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoTypeDTO"],"tgTypes":[{"type":"api-type","name":"Video"}],"nullable":true,"required":false},"video_note":{"property":"videoNote","tgPropName":"video_note","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoNoteTypeDTO"],"tgTypes":[{"type":"api-type","name":"VideoNote"}],"nullable":true,"required":false},"voice":{"property":"voice","tgPropName":"voice","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VoiceTypeDTO"],"tgTypes":[{"type":"api-type","name":"Voice"}],"nullable":true,"required":false},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"show_caption_above_media":{"property":"showCaptionAboveMedia","tgPropName":"show_caption_above_media","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"has_media_spoiler":{"property":"hasMediaSpoiler","tgPropName":"has_media_spoiler","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"checklist":{"property":"checklist","tgPropName":"checklist","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChecklistTypeDTO"],"tgTypes":[{"type":"api-type","name":"Checklist"}],"nullable":true,"required":false},"contact":{"property":"contact","tgPropName":"contact","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ContactTypeDTO"],"tgTypes":[{"type":"api-type","name":"Contact"}],"nullable":true,"required":false},"dice":{"property":"dice","tgPropName":"dice","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DiceTypeDTO"],"tgTypes":[{"type":"api-type","name":"Dice"}],"nullable":true,"required":false},"game":{"property":"game","tgPropName":"game","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GameTypeDTO"],"tgTypes":[{"type":"api-type","name":"Game"}],"nullable":true,"required":false},"poll":{"property":"poll","tgPropName":"poll","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PollTypeDTO"],"tgTypes":[{"type":"api-type","name":"Poll"}],"nullable":true,"required":false},"venue":{"property":"venue","tgPropName":"venue","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VenueTypeDTO"],"tgTypes":[{"type":"api-type","name":"Venue"}],"nullable":true,"required":false},"location":{"property":"location","tgPropName":"location","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LocationTypeDTO"],"tgTypes":[{"type":"api-type","name":"Location"}],"nullable":true,"required":false},"new_chat_members":{"property":"newChatMembers","tgPropName":"new_chat_members","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"User"}}],"nullable":true,"required":false},"left_chat_member":{"property":"leftChatMember","tgPropName":"left_chat_member","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"chat_owner_left":{"property":"chatOwnerLeft","tgPropName":"chat_owner_left","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatOwnerLeftTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatOwnerLeft"}],"nullable":true,"required":false},"chat_owner_changed":{"property":"chatOwnerChanged","tgPropName":"chat_owner_changed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatOwnerChangedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatOwnerChanged"}],"nullable":true,"required":false},"new_chat_title":{"property":"newChatTitle","tgPropName":"new_chat_title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"new_chat_photo":{"property":"newChatPhoto","tgPropName":"new_chat_photo","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PhotoSize"}}],"nullable":true,"required":false},"delete_chat_photo":{"property":"deleteChatPhoto","tgPropName":"delete_chat_photo","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"group_chat_created":{"property":"groupChatCreated","tgPropName":"group_chat_created","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"supergroup_chat_created":{"property":"supergroupChatCreated","tgPropName":"supergroup_chat_created","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"channel_chat_created":{"property":"channelChatCreated","tgPropName":"channel_chat_created","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"message_auto_delete_timer_changed":{"property":"messageAutoDeleteTimerChanged","tgPropName":"message_auto_delete_timer_changed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageAutoDeleteTimerChangedTypeDTO"],"tgTypes":[{"type":"api-type","name":"MessageAutoDeleteTimerChanged"}],"nullable":true,"required":false},"migrate_to_chat_id":{"property":"migrateToChatId","tgPropName":"migrate_to_chat_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":true,"required":false},"migrate_from_chat_id":{"property":"migrateFromChatId","tgPropName":"migrate_from_chat_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":true,"required":false},"pinned_message":{"property":"pinnedMessage","tgPropName":"pinned_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MaybeInaccessibleMessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"MaybeInaccessibleMessage"}],"nullable":true,"required":false},"invoice":{"property":"invoice","tgPropName":"invoice","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InvoiceTypeDTO"],"tgTypes":[{"type":"api-type","name":"Invoice"}],"nullable":true,"required":false},"successful_payment":{"property":"successfulPayment","tgPropName":"successful_payment","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuccessfulPaymentTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuccessfulPayment"}],"nullable":true,"required":false},"refunded_payment":{"property":"refundedPayment","tgPropName":"refunded_payment","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\RefundedPaymentTypeDTO"],"tgTypes":[{"type":"api-type","name":"RefundedPayment"}],"nullable":true,"required":false},"users_shared":{"property":"usersShared","tgPropName":"users_shared","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UsersSharedTypeDTO"],"tgTypes":[{"type":"api-type","name":"UsersShared"}],"nullable":true,"required":false},"chat_shared":{"property":"chatShared","tgPropName":"chat_shared","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatSharedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatShared"}],"nullable":true,"required":false},"gift":{"property":"gift","tgPropName":"gift","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiftInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiftInfo"}],"nullable":true,"required":false},"unique_gift":{"property":"uniqueGift","tgPropName":"unique_gift","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGiftInfo"}],"nullable":true,"required":false},"gift_upgrade_sent":{"property":"giftUpgradeSent","tgPropName":"gift_upgrade_sent","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiftInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiftInfo"}],"nullable":true,"required":false},"connected_website":{"property":"connectedWebsite","tgPropName":"connected_website","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"write_access_allowed":{"property":"writeAccessAllowed","tgPropName":"write_access_allowed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\WriteAccessAllowedTypeDTO"],"tgTypes":[{"type":"api-type","name":"WriteAccessAllowed"}],"nullable":true,"required":false},"passport_data":{"property":"passportData","tgPropName":"passport_data","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PassportDataTypeDTO"],"tgTypes":[{"type":"api-type","name":"PassportData"}],"nullable":true,"required":false},"proximity_alert_triggered":{"property":"proximityAlertTriggered","tgPropName":"proximity_alert_triggered","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ProximityAlertTriggeredTypeDTO"],"tgTypes":[{"type":"api-type","name":"ProximityAlertTriggered"}],"nullable":true,"required":false},"boost_added":{"property":"boostAdded","tgPropName":"boost_added","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatBoostAddedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatBoostAdded"}],"nullable":true,"required":false},"chat_background_set":{"property":"chatBackgroundSet","tgPropName":"chat_background_set","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatBackgroundTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatBackground"}],"nullable":true,"required":false},"checklist_tasks_done":{"property":"checklistTasksDone","tgPropName":"checklist_tasks_done","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChecklistTasksDoneTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChecklistTasksDone"}],"nullable":true,"required":false},"checklist_tasks_added":{"property":"checklistTasksAdded","tgPropName":"checklist_tasks_added","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChecklistTasksAddedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChecklistTasksAdded"}],"nullable":true,"required":false},"direct_message_price_changed":{"property":"directMessagePriceChanged","tgPropName":"direct_message_price_changed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\DirectMessagePriceChangedTypeDTO"],"tgTypes":[{"type":"api-type","name":"DirectMessagePriceChanged"}],"nullable":true,"required":false},"forum_topic_created":{"property":"forumTopicCreated","tgPropName":"forum_topic_created","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ForumTopicCreatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ForumTopicCreated"}],"nullable":true,"required":false},"forum_topic_edited":{"property":"forumTopicEdited","tgPropName":"forum_topic_edited","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ForumTopicEditedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ForumTopicEdited"}],"nullable":true,"required":false},"forum_topic_closed":{"property":"forumTopicClosed","tgPropName":"forum_topic_closed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ForumTopicClosedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ForumTopicClosed"}],"nullable":true,"required":false},"forum_topic_reopened":{"property":"forumTopicReopened","tgPropName":"forum_topic_reopened","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ForumTopicReopenedTypeDTO"],"tgTypes":[{"type":"api-type","name":"ForumTopicReopened"}],"nullable":true,"required":false},"general_forum_topic_hidden":{"property":"generalForumTopicHidden","tgPropName":"general_forum_topic_hidden","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GeneralForumTopicHiddenTypeDTO"],"tgTypes":[{"type":"api-type","name":"GeneralForumTopicHidden"}],"nullable":true,"required":false},"general_forum_topic_unhidden":{"property":"generalForumTopicUnhidden","tgPropName":"general_forum_topic_unhidden","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GeneralForumTopicUnhiddenTypeDTO"],"tgTypes":[{"type":"api-type","name":"GeneralForumTopicUnhidden"}],"nullable":true,"required":false},"giveaway_created":{"property":"giveawayCreated","tgPropName":"giveaway_created","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiveawayCreatedTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiveawayCreated"}],"nullable":true,"required":false},"giveaway":{"property":"giveaway","tgPropName":"giveaway","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiveawayTypeDTO"],"tgTypes":[{"type":"api-type","name":"Giveaway"}],"nullable":true,"required":false},"giveaway_winners":{"property":"giveawayWinners","tgPropName":"giveaway_winners","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiveawayWinnersTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiveawayWinners"}],"nullable":true,"required":false},"giveaway_completed":{"property":"giveawayCompleted","tgPropName":"giveaway_completed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiveawayCompletedTypeDTO"],"tgTypes":[{"type":"api-type","name":"GiveawayCompleted"}],"nullable":true,"required":false},"paid_message_price_changed":{"property":"paidMessagePriceChanged","tgPropName":"paid_message_price_changed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PaidMessagePriceChangedTypeDTO"],"tgTypes":[{"type":"api-type","name":"PaidMessagePriceChanged"}],"nullable":true,"required":false},"suggested_post_approved":{"property":"suggestedPostApproved","tgPropName":"suggested_post_approved","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostApprovedTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostApproved"}],"nullable":true,"required":false},"suggested_post_approval_failed":{"property":"suggestedPostApprovalFailed","tgPropName":"suggested_post_approval_failed","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostApprovalFailedTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostApprovalFailed"}],"nullable":true,"required":false},"suggested_post_declined":{"property":"suggestedPostDeclined","tgPropName":"suggested_post_declined","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostDeclinedTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostDeclined"}],"nullable":true,"required":false},"suggested_post_paid":{"property":"suggestedPostPaid","tgPropName":"suggested_post_paid","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostPaidTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostPaid"}],"nullable":true,"required":false},"suggested_post_refunded":{"property":"suggestedPostRefunded","tgPropName":"suggested_post_refunded","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostRefundedTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostRefunded"}],"nullable":true,"required":false},"video_chat_scheduled":{"property":"videoChatScheduled","tgPropName":"video_chat_scheduled","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoChatScheduledTypeDTO"],"tgTypes":[{"type":"api-type","name":"VideoChatScheduled"}],"nullable":true,"required":false},"video_chat_started":{"property":"videoChatStarted","tgPropName":"video_chat_started","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoChatStartedTypeDTO"],"tgTypes":[{"type":"api-type","name":"VideoChatStarted"}],"nullable":true,"required":false},"video_chat_ended":{"property":"videoChatEnded","tgPropName":"video_chat_ended","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoChatEndedTypeDTO"],"tgTypes":[{"type":"api-type","name":"VideoChatEnded"}],"nullable":true,"required":false},"video_chat_participants_invited":{"property":"videoChatParticipantsInvited","tgPropName":"video_chat_participants_invited","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\VideoChatParticipantsInvitedTypeDTO"],"tgTypes":[{"type":"api-type","name":"VideoChatParticipantsInvited"}],"nullable":true,"required":false},"web_app_data":{"property":"webAppData","tgPropName":"web_app_data","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\WebAppDataTypeDTO"],"tgTypes":[{"type":"api-type","name":"WebAppData"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false}}
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
