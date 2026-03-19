<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change or CustomMethodEnum extends TgApiEntityScopeEnumContract')]
#[Description('List of Telegram Bot Api Types')]
#[See('https://core.telegram.org/bots/api#available-types')]
enum TgApiTypesEnum: string implements TgApiEntityEnumContract
{
    #[Description('This [object](https://core.telegram.org/bots/api#available-types) represents an incoming update.; ; At most **one** of the optional parameters can be present in any given update.')]
    case Update = \BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO::class;
    #[Description('Describes the current status of a webhook.')]
    case WebhookInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\WebhookInfoTypeDTO::class;
    #[Description('This object represents a Telegram user or bot.')]
    case User = \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO::class;
    #[Description('This object represents a chat.')]
    case Chat = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO::class;
    #[Description('This object contains full information about a chat.')]
    case ChatFullInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatFullInfoTypeDTO::class;
    #[Description('This object represents a message.')]
    case Message = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO::class;
    #[Description('This object represents a unique message identifier.')]
    case MessageId = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageIdTypeDTO::class;
    #[Description('This object describes a message that was deleted or is otherwise inaccessible to the bot.')]
    case InaccessibleMessage = \BAGArt\TelegramBot\TgApi\Types\DTO\InaccessibleMessageTypeDTO::class;
    #[Description('This object describes a message that can be inaccessible to the bot. It can be one of; ; -   [Message](https://core.telegram.org/bots/api#message); -   [InaccessibleMessage](https://core.telegram.org/bots/api#inaccessiblemessage)')]
    case MaybeInaccessibleMessage = \BAGArt\TelegramBot\TgApi\Types\DTO\MaybeInaccessibleMessageTypeDTO::class;
    #[Description('This object represents one special entity in a text message. For example, hashtags, usernames, URLs, etc.')]
    case MessageEntity = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageEntityTypeDTO::class;
    #[Description('This object contains information about the quoted part of a message that is replied to by the given message.')]
    case TextQuote = \BAGArt\TelegramBot\TgApi\Types\DTO\TextQuoteTypeDTO::class;
    #[Description('This object contains information about a message that is being replied to, which may come from another chat or forum topic.')]
    case ExternalReplyInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\ExternalReplyInfoTypeDTO::class;
    #[Description('Describes reply parameters for the message that is being sent.')]
    case ReplyParameters = \BAGArt\TelegramBot\TgApi\Types\DTO\ReplyParametersTypeDTO::class;
    #[Description('This object describes the origin of a message. It can be one of; ; -   [MessageOriginUser](https://core.telegram.org/bots/api#messageoriginuser); -   [MessageOriginHiddenUser](https://core.telegram.org/bots/api#messageoriginhiddenuser); -   [MessageOriginChat](https://core.telegram.org/bots/api#messageoriginchat); -   [MessageOriginChannel](https://core.telegram.org/bots/api#messageoriginchannel)')]
    case MessageOrigin = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageOriginTypeDTO::class;
    #[Description('The message was originally sent by a known user.')]
    case MessageOriginUser = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageOriginUserTypeDTO::class;
    #[Description('The message was originally sent by an unknown user.')]
    case MessageOriginHiddenUser = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageOriginHiddenUserTypeDTO::class;
    #[Description('The message was originally sent on behalf of a chat to a group chat.')]
    case MessageOriginChat = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageOriginChatTypeDTO::class;
    #[Description('The message was originally sent to a channel chat.')]
    case MessageOriginChannel = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageOriginChannelTypeDTO::class;
    #[Description('This object represents one size of a photo or a [file](https://core.telegram.org/bots/api#document) / [sticker](https://core.telegram.org/bots/api#sticker) thumbnail.')]
    case PhotoSize = \BAGArt\TelegramBot\TgApi\Types\DTO\PhotoSizeTypeDTO::class;
    #[Description('This object represents an animation file (GIF or H.264/MPEG-4 AVC video without sound).')]
    case Animation = \BAGArt\TelegramBot\TgApi\Types\DTO\AnimationTypeDTO::class;
    #[Description('This object represents an audio file to be treated as music by the Telegram clients.')]
    case Audio = \BAGArt\TelegramBot\TgApi\Types\DTO\AudioTypeDTO::class;
    #[Description('This object represents a general file (as opposed to [photos](https://core.telegram.org/bots/api#photosize), [voice messages](https://core.telegram.org/bots/api#voice) and [audio files](https://core.telegram.org/bots/api#audio)).')]
    case Document = \BAGArt\TelegramBot\TgApi\Types\DTO\DocumentTypeDTO::class;
    #[Description('This object represents a story.')]
    case Story = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryTypeDTO::class;
    #[Description('This object represents a video file of a specific quality.')]
    case VideoQuality = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoQualityTypeDTO::class;
    #[Description('This object represents a video file.')]
    case Video = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoTypeDTO::class;
    #[Description('This object represents a [video message](https://telegram.org/blog/video-messages-and-telescope) (available in Telegram apps as of [v.4.0](https://telegram.org/blog/video-messages-and-telescope)).')]
    case VideoNote = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoNoteTypeDTO::class;
    #[Description('This object represents a voice note.')]
    case Voice = \BAGArt\TelegramBot\TgApi\Types\DTO\VoiceTypeDTO::class;
    #[Description('Describes the paid media added to a message.')]
    case PaidMediaInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaInfoTypeDTO::class;
    #[Description('This object describes paid media. Currently, it can be one of; ; -   [PaidMediaPreview](https://core.telegram.org/bots/api#paidmediapreview); -   [PaidMediaPhoto](https://core.telegram.org/bots/api#paidmediaphoto); -   [PaidMediaVideo](https://core.telegram.org/bots/api#paidmediavideo)')]
    case PaidMedia = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaTypeDTO::class;
    #[Description('The paid media isn"t available before the payment.')]
    case PaidMediaPreview = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaPreviewTypeDTO::class;
    #[Description('The paid media is a photo.')]
    case PaidMediaPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaPhotoTypeDTO::class;
    #[Description('The paid media is a video.')]
    case PaidMediaVideo = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaVideoTypeDTO::class;
    #[Description('This object represents a phone contact.')]
    case Contact = \BAGArt\TelegramBot\TgApi\Types\DTO\ContactTypeDTO::class;
    #[Description('This object represents an animated emoji that displays a random value.')]
    case Dice = \BAGArt\TelegramBot\TgApi\Types\DTO\DiceTypeDTO::class;
    #[Description('This object contains information about one answer option in a poll.')]
    case PollOption = \BAGArt\TelegramBot\TgApi\Types\DTO\PollOptionTypeDTO::class;
    #[Description('This object contains information about one answer option in a poll to be sent.')]
    case InputPollOption = \BAGArt\TelegramBot\TgApi\Types\DTO\InputPollOptionTypeDTO::class;
    #[Description('This object represents an answer of a user in a non-anonymous poll.')]
    case PollAnswer = \BAGArt\TelegramBot\TgApi\Types\DTO\PollAnswerTypeDTO::class;
    #[Description('This object contains information about a poll.')]
    case Poll = \BAGArt\TelegramBot\TgApi\Types\DTO\PollTypeDTO::class;
    #[Description('Describes a task in a checklist.')]
    case ChecklistTask = \BAGArt\TelegramBot\TgApi\Types\DTO\ChecklistTaskTypeDTO::class;
    #[Description('Describes a checklist.')]
    case Checklist = \BAGArt\TelegramBot\TgApi\Types\DTO\ChecklistTypeDTO::class;
    #[Description('Describes a task to add to a checklist.')]
    case InputChecklistTask = \BAGArt\TelegramBot\TgApi\Types\DTO\InputChecklistTaskTypeDTO::class;
    #[Description('Describes a checklist to create.')]
    case InputChecklist = \BAGArt\TelegramBot\TgApi\Types\DTO\InputChecklistTypeDTO::class;
    #[Description('Describes a service message about checklist tasks marked as done or not done.')]
    case ChecklistTasksDone = \BAGArt\TelegramBot\TgApi\Types\DTO\ChecklistTasksDoneTypeDTO::class;
    #[Description('Describes a service message about tasks added to a checklist.')]
    case ChecklistTasksAdded = \BAGArt\TelegramBot\TgApi\Types\DTO\ChecklistTasksAddedTypeDTO::class;
    #[Description('This object represents a point on the map.')]
    case Location = \BAGArt\TelegramBot\TgApi\Types\DTO\LocationTypeDTO::class;
    #[Description('This object represents a venue.')]
    case Venue = \BAGArt\TelegramBot\TgApi\Types\DTO\VenueTypeDTO::class;
    #[Description('Describes data sent from a [Web App](https://core.telegram.org/bots/webapps) to the bot.')]
    case WebAppData = \BAGArt\TelegramBot\TgApi\Types\DTO\WebAppDataTypeDTO::class;
    #[Description('This object represents the content of a service message, sent whenever a user in the chat triggers a proximity alert set by another user.')]
    case ProximityAlertTriggered = \BAGArt\TelegramBot\TgApi\Types\DTO\ProximityAlertTriggeredTypeDTO::class;
    #[Description('This object represents a service message about a change in auto-delete timer settings.')]
    case MessageAutoDeleteTimerChanged = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageAutoDeleteTimerChangedTypeDTO::class;
    #[Description('This object represents a service message about a user boosting a chat.')]
    case ChatBoostAdded = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostAddedTypeDTO::class;
    #[Description('This object describes the way a background is filled based on the selected colors. Currently, it can be one of; ; -   [BackgroundFillSolid](https://core.telegram.org/bots/api#backgroundfillsolid); -   [BackgroundFillGradient](https://core.telegram.org/bots/api#backgroundfillgradient); -   [BackgroundFillFreeformGradient](https://core.telegram.org/bots/api#backgroundfillfreeformgradient)')]
    case BackgroundFill = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundFillTypeDTO::class;
    #[Description('The background is filled using the selected color.')]
    case BackgroundFillSolid = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundFillSolidTypeDTO::class;
    #[Description('The background is a gradient fill.')]
    case BackgroundFillGradient = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundFillGradientTypeDTO::class;
    #[Description('The background is a freeform gradient that rotates after every message in the chat.')]
    case BackgroundFillFreeformGradient = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundFillFreeformGradientTypeDTO::class;
    #[Description('This object describes the type of a background. Currently, it can be one of; ; -   [BackgroundTypeFill](https://core.telegram.org/bots/api#backgroundtypefill); -   [BackgroundTypeWallpaper](https://core.telegram.org/bots/api#backgroundtypewallpaper); -   [BackgroundTypePattern](https://core.telegram.org/bots/api#backgroundtypepattern); -   [BackgroundTypeChatTheme](https://core.telegram.org/bots/api#backgroundtypechattheme)')]
    case BackgroundType = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundTypeTypeDTO::class;
    #[Description('The background is automatically filled based on the selected colors.')]
    case BackgroundTypeFill = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundTypeFillTypeDTO::class;
    #[Description('The background is a wallpaper in the JPEG format.')]
    case BackgroundTypeWallpaper = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundTypeWallpaperTypeDTO::class;
    #[Description('The background is a .PNG or .TGV (gzipped subset of SVG with MIME type “application/x-tgwallpattern”) pattern to be combined with the background fill chosen by the user.')]
    case BackgroundTypePattern = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundTypePatternTypeDTO::class;
    #[Description('The background is taken directly from a built-in chat theme.')]
    case BackgroundTypeChatTheme = \BAGArt\TelegramBot\TgApi\Types\DTO\BackgroundTypeChatThemeTypeDTO::class;
    #[Description('This object represents a chat background.')]
    case ChatBackground = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBackgroundTypeDTO::class;
    #[Description('This object represents a service message about a new forum topic created in the chat.')]
    case ForumTopicCreated = \BAGArt\TelegramBot\TgApi\Types\DTO\ForumTopicCreatedTypeDTO::class;
    #[Description('This object represents a service message about a forum topic closed in the chat. Currently holds no information.')]
    case ForumTopicClosed = \BAGArt\TelegramBot\TgApi\Types\DTO\ForumTopicClosedTypeDTO::class;
    #[Description('This object represents a service message about an edited forum topic.')]
    case ForumTopicEdited = \BAGArt\TelegramBot\TgApi\Types\DTO\ForumTopicEditedTypeDTO::class;
    #[Description('This object represents a service message about a forum topic reopened in the chat. Currently holds no information.')]
    case ForumTopicReopened = \BAGArt\TelegramBot\TgApi\Types\DTO\ForumTopicReopenedTypeDTO::class;
    #[Description('This object represents a service message about General forum topic hidden in the chat. Currently holds no information.')]
    case GeneralForumTopicHidden = \BAGArt\TelegramBot\TgApi\Types\DTO\GeneralForumTopicHiddenTypeDTO::class;
    #[Description('This object represents a service message about General forum topic unhidden in the chat. Currently holds no information.')]
    case GeneralForumTopicUnhidden = \BAGArt\TelegramBot\TgApi\Types\DTO\GeneralForumTopicUnhiddenTypeDTO::class;
    #[Description('This object contains information about a user that was shared with the bot using a [KeyboardButtonRequestUsers](https://core.telegram.org/bots/api#keyboardbuttonrequestusers) button.')]
    case SharedUser = \BAGArt\TelegramBot\TgApi\Types\DTO\SharedUserTypeDTO::class;
    #[Description('This object contains information about the users whose identifiers were shared with the bot using a [KeyboardButtonRequestUsers](https://core.telegram.org/bots/api#keyboardbuttonrequestusers) button.')]
    case UsersShared = \BAGArt\TelegramBot\TgApi\Types\DTO\UsersSharedTypeDTO::class;
    #[Description('This object contains information about a chat that was shared with the bot using a [KeyboardButtonRequestChat](https://core.telegram.org/bots/api#keyboardbuttonrequestchat) button.')]
    case ChatShared = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatSharedTypeDTO::class;
    #[Description('This object represents a service message about a user allowing a bot to write messages after adding it to the attachment menu, launching a Web App from a link, or accepting an explicit request from a Web App sent by the method [requestWriteAccess](https://core.telegram.org/bots/webapps#initializing-mini-apps).')]
    case WriteAccessAllowed = \BAGArt\TelegramBot\TgApi\Types\DTO\WriteAccessAllowedTypeDTO::class;
    #[Description('This object represents a service message about a video chat scheduled in the chat.')]
    case VideoChatScheduled = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoChatScheduledTypeDTO::class;
    #[Description('This object represents a service message about a video chat started in the chat. Currently holds no information.')]
    case VideoChatStarted = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoChatStartedTypeDTO::class;
    #[Description('This object represents a service message about a video chat ended in the chat.')]
    case VideoChatEnded = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoChatEndedTypeDTO::class;
    #[Description('This object represents a service message about new members invited to a video chat.')]
    case VideoChatParticipantsInvited = \BAGArt\TelegramBot\TgApi\Types\DTO\VideoChatParticipantsInvitedTypeDTO::class;
    #[Description('Describes a service message about a change in the price of paid messages within a chat.')]
    case PaidMessagePriceChanged = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMessagePriceChangedTypeDTO::class;
    #[Description('Describes a service message about a change in the price of direct messages sent to a channel chat.')]
    case DirectMessagePriceChanged = \BAGArt\TelegramBot\TgApi\Types\DTO\DirectMessagePriceChangedTypeDTO::class;
    #[Description('Describes a service message about the approval of a suggested post.')]
    case SuggestedPostApproved = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostApprovedTypeDTO::class;
    #[Description('Describes a service message about the failed approval of a suggested post. Currently, only caused by insufficient user funds at the time of approval.')]
    case SuggestedPostApprovalFailed = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostApprovalFailedTypeDTO::class;
    #[Description('Describes a service message about the rejection of a suggested post.')]
    case SuggestedPostDeclined = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostDeclinedTypeDTO::class;
    #[Description('Describes a service message about a successful payment for a suggested post.')]
    case SuggestedPostPaid = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostPaidTypeDTO::class;
    #[Description('Describes a service message about a payment refund for a suggested post.')]
    case SuggestedPostRefunded = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostRefundedTypeDTO::class;
    #[Description('This object represents a service message about the creation of a scheduled giveaway.')]
    case GiveawayCreated = \BAGArt\TelegramBot\TgApi\Types\DTO\GiveawayCreatedTypeDTO::class;
    #[Description('This object represents a message about a scheduled giveaway.')]
    case Giveaway = \BAGArt\TelegramBot\TgApi\Types\DTO\GiveawayTypeDTO::class;
    #[Description('This object represents a message about the completion of a giveaway with public winners.')]
    case GiveawayWinners = \BAGArt\TelegramBot\TgApi\Types\DTO\GiveawayWinnersTypeDTO::class;
    #[Description('This object represents a service message about the completion of a giveaway without public winners.')]
    case GiveawayCompleted = \BAGArt\TelegramBot\TgApi\Types\DTO\GiveawayCompletedTypeDTO::class;
    #[Description('Describes the options used for link preview generation.')]
    case LinkPreviewOptions = \BAGArt\TelegramBot\TgApi\Types\DTO\LinkPreviewOptionsTypeDTO::class;
    #[Description('Describes the price of a suggested post.')]
    case SuggestedPostPrice = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostPriceTypeDTO::class;
    #[Description('Contains information about a suggested post.')]
    case SuggestedPostInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostInfoTypeDTO::class;
    #[Description('Contains parameters of a post that is being suggested by the bot.')]
    case SuggestedPostParameters = \BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostParametersTypeDTO::class;
    #[Description('Describes a topic of a direct messages chat.')]
    case DirectMessagesTopic = \BAGArt\TelegramBot\TgApi\Types\DTO\DirectMessagesTopicTypeDTO::class;
    #[Description('This object represent a user"s profile pictures.')]
    case UserProfilePhotos = \BAGArt\TelegramBot\TgApi\Types\DTO\UserProfilePhotosTypeDTO::class;
    #[Description('This object represents the audios displayed on a user"s profile.')]
    case UserProfileAudios = \BAGArt\TelegramBot\TgApi\Types\DTO\UserProfileAudiosTypeDTO::class;
    #[Description('This object represents a file ready to be downloaded. The file can be downloaded via the link `https://api.telegram.org/file/bot<token>/<file_path>`. It is guaranteed that the link will be valid for at least 1 hour. When the link expires, a new one can be requested by calling [getFile](https://core.telegram.org/bots/api#getfile).; ; > The maximum file size to download is 20 MB')]
    case File = \BAGArt\TelegramBot\TgApi\Types\DTO\FileTypeDTO::class;
    #[Description('Describes a [Web App](https://core.telegram.org/bots/webapps).')]
    case WebAppInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\WebAppInfoTypeDTO::class;
    #[Description('This object represents a [custom keyboard](https://core.telegram.org/bots/features#keyboards) with reply options (see [Introduction to bots](https://core.telegram.org/bots/features#keyboards) for details and examples). Not supported in channels and for messages sent on behalf of a Telegram Business account.')]
    case ReplyKeyboardMarkup = \BAGArt\TelegramBot\TgApi\Types\DTO\ReplyKeyboardMarkupTypeDTO::class;
    #[Description('This object represents one button of the reply keyboard. At most one of the fields other than _text_, _icon\_custom\_emoji\_id_, and _style_ must be used to specify the type of the button. For simple text buttons, _String_ can be used instead of this object to specify the button text.')]
    case KeyboardButton = \BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonTypeDTO::class;
    #[Description('This object defines the criteria used to request suitable users. Information about the selected users will be shared with the bot when the corresponding button is pressed. [More about requesting users »](https://core.telegram.org/bots/features#chat-and-user-selection)')]
    case KeyboardButtonRequestUsers = \BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonRequestUsersTypeDTO::class;
    #[Description('This object defines the criteria used to request a suitable chat. Information about the selected chat will be shared with the bot when the corresponding button is pressed. The bot will be granted requested rights in the chat if appropriate. [More about requesting chats »](https://core.telegram.org/bots/features#chat-and-user-selection).')]
    case KeyboardButtonRequestChat = \BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonRequestChatTypeDTO::class;
    #[Description('This object represents type of a poll, which is allowed to be created and sent when the corresponding button is pressed.')]
    case KeyboardButtonPollType = \BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonPollTypeTypeDTO::class;
    #[Description('Upon receiving a message with this object, Telegram clients will remove the current custom keyboard and display the default letter-keyboard. By default, custom keyboards are displayed until a new keyboard is sent by a bot. An exception is made for one-time keyboards that are hidden immediately after the user presses a button (see [ReplyKeyboardMarkup](https://core.telegram.org/bots/api#replykeyboardmarkup)). Not supported in channels and for messages sent on behalf of a Telegram Business account.')]
    case ReplyKeyboardRemove = \BAGArt\TelegramBot\TgApi\Types\DTO\ReplyKeyboardRemoveTypeDTO::class;
    #[Description('This object represents an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) that appears right next to the message it belongs to.')]
    case InlineKeyboardMarkup = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO::class;
    #[Description('This object represents one button of an inline keyboard. Exactly one of the fields other than _text_, _icon\_custom\_emoji\_id_, and _style_ must be used to specify the type of the button.')]
    case InlineKeyboardButton = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardButtonTypeDTO::class;
    #[Description('This object represents a parameter of the inline keyboard button used to automatically authorize a user. Serves as a great replacement for the [Telegram Login Widget](https://core.telegram.org/widgets/login) when the user is coming from Telegram. All the user needs to do is tap/click a button and confirm that they want to log in:; ; [![TITLE](/file/811140909/1631/20k1Z53eiyY.23995/c541e89b74253623d9 "TITLE")](https://core.telegram.org/file/811140015/1734/8VZFkwWXalM.97872/6127fa62d8a0bf2b3c); ; Telegram apps support these buttons as of [version 5.7](https://telegram.org/blog/privacy-discussions-web-bots#meet-seamless-web-bots).; ; > Sample bot: [@discussbot](https://t.me/discussbot)')]
    case LoginUrl = \BAGArt\TelegramBot\TgApi\Types\DTO\LoginUrlTypeDTO::class;
    #[Description('This object represents an inline button that switches the current user to inline mode in a chosen chat, with an optional default inline query.')]
    case SwitchInlineQueryChosenChat = \BAGArt\TelegramBot\TgApi\Types\DTO\SwitchInlineQueryChosenChatTypeDTO::class;
    #[Description('This object represents an inline keyboard button that copies specified text to the clipboard.')]
    case CopyTextButton = \BAGArt\TelegramBot\TgApi\Types\DTO\CopyTextButtonTypeDTO::class;
    #[Description('This object represents an incoming callback query from a callback button in an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards). If the button that originated the query was attached to a message sent by the bot, the field _message_ will be present. If the button was attached to a message sent via the bot (in [inline mode](https://core.telegram.org/bots/api#inline-mode)), the field _inline\_message\_id_ will be present. Exactly one of the fields _data_ or _game\_short\_name_ will be present.; ; > **NOTE:** After the user presses a callback button, Telegram clients will display a progress bar until you call [answerCallbackQuery](https://core.telegram.org/bots/api#answercallbackquery). It is, therefore, necessary to react by calling [answerCallbackQuery](https://core.telegram.org/bots/api#answercallbackquery) even if no notification to the user is needed (e.g., without specifying any of the optional parameters).')]
    case CallbackQuery = \BAGArt\TelegramBot\TgApi\Types\DTO\CallbackQueryTypeDTO::class;
    #[Description('Upon receiving a message with this object, Telegram clients will display a reply interface to the user (act as if the user has selected the bot"s message and tapped "Reply"). This can be extremely useful if you want to create user-friendly step-by-step interfaces without having to sacrifice [privacy mode](https://core.telegram.org/bots/features#privacy-mode). Not supported in channels and for messages sent on behalf of a Telegram Business account.; ; > **Example:** A [poll bot](https://t.me/PollBot) for groups runs in privacy mode (only receives commands, replies to its messages and mentions). There could be two ways to create a new poll:; > ; > -   Explain the user how to send a command with parameters (e.g. /newpoll question answer1 answer2). May be appealing for hardcore users but lacks modern day polish.; > -   Guide the user through a step-by-step process. "Please send me your question", "Cool, now let"s add the first answer option", "Great. Keep adding answer options, then send /done when you"re ready".; > ; > The last option is definitely more attractive. And if you use [ForceReply](https://core.telegram.org/bots/api#forcereply) in your bot"s questions, it will receive the user"s answers even if it only receives replies, commands and mentions - without any extra work for the user.')]
    case ForceReply = \BAGArt\TelegramBot\TgApi\Types\DTO\ForceReplyTypeDTO::class;
    #[Description('This object represents a chat photo.')]
    case ChatPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatPhotoTypeDTO::class;
    #[Description('Represents an invite link for a chat.')]
    case ChatInviteLink = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatInviteLinkTypeDTO::class;
    #[Description('Represents the rights of an administrator in a chat.')]
    case ChatAdministratorRights = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatAdministratorRightsTypeDTO::class;
    #[Description('This object represents changes in the status of a chat member.')]
    case ChatMemberUpdated = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberUpdatedTypeDTO::class;
    #[Description('This object contains information about one member of a chat. Currently, the following 6 types of chat members are supported:; ; -   [ChatMemberOwner](https://core.telegram.org/bots/api#chatmemberowner); -   [ChatMemberAdministrator](https://core.telegram.org/bots/api#chatmemberadministrator); -   [ChatMemberMember](https://core.telegram.org/bots/api#chatmembermember); -   [ChatMemberRestricted](https://core.telegram.org/bots/api#chatmemberrestricted); -   [ChatMemberLeft](https://core.telegram.org/bots/api#chatmemberleft); -   [ChatMemberBanned](https://core.telegram.org/bots/api#chatmemberbanned)')]
    case ChatMember = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberTypeDTO::class;
    #[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that owns the chat and has all administrator privileges.')]
    case ChatMemberOwner = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberOwnerTypeDTO::class;
    #[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that has some additional privileges.')]
    case ChatMemberAdministrator = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberAdministratorTypeDTO::class;
    #[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that has no additional privileges or restrictions.')]
    case ChatMemberMember = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberMemberTypeDTO::class;
    #[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that is under certain restrictions in the chat. Supergroups only.')]
    case ChatMemberRestricted = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberRestrictedTypeDTO::class;
    #[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that isn"t currently a member of the chat, but may join it themselves.')]
    case ChatMemberLeft = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberLeftTypeDTO::class;
    #[Description('Represents a [chat member](https://core.telegram.org/bots/api#chatmember) that was banned in the chat and can"t return to the chat or view chat messages.')]
    case ChatMemberBanned = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatMemberBannedTypeDTO::class;
    #[Description('Represents a join request sent to a chat.')]
    case ChatJoinRequest = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatJoinRequestTypeDTO::class;
    #[Description('Describes actions that a non-administrator user is allowed to take in a chat.')]
    case ChatPermissions = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatPermissionsTypeDTO::class;
    #[Description('Describes the birthdate of a user.')]
    case Birthdate = \BAGArt\TelegramBot\TgApi\Types\DTO\BirthdateTypeDTO::class;
    #[Description('Contains information about the start page settings of a Telegram Business account.')]
    case BusinessIntro = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessIntroTypeDTO::class;
    #[Description('Contains information about the location of a Telegram Business account.')]
    case BusinessLocation = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessLocationTypeDTO::class;
    #[Description('Describes an interval of time during which a business is open.')]
    case BusinessOpeningHoursInterval = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessOpeningHoursIntervalTypeDTO::class;
    #[Description('Describes the opening hours of a business.')]
    case BusinessOpeningHours = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessOpeningHoursTypeDTO::class;
    #[Description('This object describes the rating of a user based on their Telegram Star spendings.')]
    case UserRating = \BAGArt\TelegramBot\TgApi\Types\DTO\UserRatingTypeDTO::class;
    #[Description('Describes the position of a clickable area within a story.')]
    case StoryAreaPosition = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaPositionTypeDTO::class;
    #[Description('Describes the physical address of a location.')]
    case LocationAddress = \BAGArt\TelegramBot\TgApi\Types\DTO\LocationAddressTypeDTO::class;
    #[Description('Describes the type of a clickable area on a story. Currently, it can be one of; ; -   [StoryAreaTypeLocation](https://core.telegram.org/bots/api#storyareatypelocation); -   [StoryAreaTypeSuggestedReaction](https://core.telegram.org/bots/api#storyareatypesuggestedreaction); -   [StoryAreaTypeLink](https://core.telegram.org/bots/api#storyareatypelink); -   [StoryAreaTypeWeather](https://core.telegram.org/bots/api#storyareatypeweather); -   [StoryAreaTypeUniqueGift](https://core.telegram.org/bots/api#storyareatypeuniquegift)')]
    case StoryAreaType = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeTypeDTO::class;
    #[Description('Describes a story area pointing to a location. Currently, a story can have up to 10 location areas.')]
    case StoryAreaTypeLocation = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeLocationTypeDTO::class;
    #[Description('Describes a story area pointing to a suggested reaction. Currently, a story can have up to 5 suggested reaction areas.')]
    case StoryAreaTypeSuggestedReaction = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeSuggestedReactionTypeDTO::class;
    #[Description('Describes a story area pointing to an HTTP or tg:// link. Currently, a story can have up to 3 link areas.')]
    case StoryAreaTypeLink = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeLinkTypeDTO::class;
    #[Description('Describes a story area containing weather information. Currently, a story can have up to 3 weather areas.')]
    case StoryAreaTypeWeather = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeWeatherTypeDTO::class;
    #[Description('Describes a story area pointing to a unique gift. Currently, a story can have at most 1 unique gift area.')]
    case StoryAreaTypeUniqueGift = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeUniqueGiftTypeDTO::class;
    #[Description('Describes a clickable area on a story media.')]
    case StoryArea = \BAGArt\TelegramBot\TgApi\Types\DTO\StoryAreaTypeDTO::class;
    #[Description('Represents a location to which a chat is connected.')]
    case ChatLocation = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatLocationTypeDTO::class;
    #[Description('This object describes the type of a reaction. Currently, it can be one of; ; -   [ReactionTypeEmoji](https://core.telegram.org/bots/api#reactiontypeemoji); -   [ReactionTypeCustomEmoji](https://core.telegram.org/bots/api#reactiontypecustomemoji); -   [ReactionTypePaid](https://core.telegram.org/bots/api#reactiontypepaid)')]
    case ReactionType = \BAGArt\TelegramBot\TgApi\Types\DTO\ReactionTypeTypeDTO::class;
    #[Description('The reaction is based on an emoji.')]
    case ReactionTypeEmoji = \BAGArt\TelegramBot\TgApi\Types\DTO\ReactionTypeEmojiTypeDTO::class;
    #[Description('The reaction is based on a custom emoji.')]
    case ReactionTypeCustomEmoji = \BAGArt\TelegramBot\TgApi\Types\DTO\ReactionTypeCustomEmojiTypeDTO::class;
    #[Description('The reaction is paid.')]
    case ReactionTypePaid = \BAGArt\TelegramBot\TgApi\Types\DTO\ReactionTypePaidTypeDTO::class;
    #[Description('Represents a reaction added to a message along with the number of times it was added.')]
    case ReactionCount = \BAGArt\TelegramBot\TgApi\Types\DTO\ReactionCountTypeDTO::class;
    #[Description('This object represents a change of a reaction on a message performed by a user.')]
    case MessageReactionUpdated = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageReactionUpdatedTypeDTO::class;
    #[Description('This object represents reaction changes on a message with anonymous reactions.')]
    case MessageReactionCountUpdated = \BAGArt\TelegramBot\TgApi\Types\DTO\MessageReactionCountUpdatedTypeDTO::class;
    #[Description('This object represents a forum topic.')]
    case ForumTopic = \BAGArt\TelegramBot\TgApi\Types\DTO\ForumTopicTypeDTO::class;
    #[Description('This object describes the background of a gift.')]
    case GiftBackground = \BAGArt\TelegramBot\TgApi\Types\DTO\GiftBackgroundTypeDTO::class;
    #[Description('This object represents a gift that can be sent by the bot.')]
    case Gift = \BAGArt\TelegramBot\TgApi\Types\DTO\GiftTypeDTO::class;
    #[Description('This object represent a list of gifts.')]
    case Gifts = \BAGArt\TelegramBot\TgApi\Types\DTO\GiftsTypeDTO::class;
    #[Description('This object describes the model of a unique gift.')]
    case UniqueGiftModel = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftModelTypeDTO::class;
    #[Description('This object describes the symbol shown on the pattern of a unique gift.')]
    case UniqueGiftSymbol = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftSymbolTypeDTO::class;
    #[Description('This object describes the colors of the backdrop of a unique gift.')]
    case UniqueGiftBackdropColors = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftBackdropColorsTypeDTO::class;
    #[Description('This object describes the backdrop of a unique gift.')]
    case UniqueGiftBackdrop = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftBackdropTypeDTO::class;
    #[Description('This object contains information about the color scheme for a user"s name, message replies and link previews based on a unique gift.')]
    case UniqueGiftColors = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftColorsTypeDTO::class;
    #[Description('This object describes a unique gift that was upgraded from a regular gift.')]
    case UniqueGift = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftTypeDTO::class;
    #[Description('Describes a service message about a regular gift that was sent or received.')]
    case GiftInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\GiftInfoTypeDTO::class;
    #[Description('Describes a service message about a unique gift that was sent or received.')]
    case UniqueGiftInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftInfoTypeDTO::class;
    #[Description('This object describes a gift received and owned by a user or a chat. Currently, it can be one of; ; -   [OwnedGiftRegular](https://core.telegram.org/bots/api#ownedgiftregular); -   [OwnedGiftUnique](https://core.telegram.org/bots/api#ownedgiftunique)')]
    case OwnedGift = \BAGArt\TelegramBot\TgApi\Types\DTO\OwnedGiftTypeDTO::class;
    #[Description('Describes a regular gift owned by a user or a chat.')]
    case OwnedGiftRegular = \BAGArt\TelegramBot\TgApi\Types\DTO\OwnedGiftRegularTypeDTO::class;
    #[Description('Describes a unique gift received and owned by a user or a chat.')]
    case OwnedGiftUnique = \BAGArt\TelegramBot\TgApi\Types\DTO\OwnedGiftUniqueTypeDTO::class;
    #[Description('Contains the list of gifts received and owned by a user or a chat.')]
    case OwnedGifts = \BAGArt\TelegramBot\TgApi\Types\DTO\OwnedGiftsTypeDTO::class;
    #[Description('This object describes the types of gifts that can be gifted to a user or a chat.')]
    case AcceptedGiftTypes = \BAGArt\TelegramBot\TgApi\Types\DTO\AcceptedGiftTypesTypeDTO::class;
    #[Description('Describes an amount of Telegram Stars.')]
    case StarAmount = \BAGArt\TelegramBot\TgApi\Types\DTO\StarAmountTypeDTO::class;
    #[Description('This object represents a bot command.')]
    case BotCommand = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandTypeDTO::class;
    #[Description('This object represents the scope to which bot commands are applied. Currently, the following 7 scopes are supported:; ; -   [BotCommandScopeDefault](https://core.telegram.org/bots/api#botcommandscopedefault); -   [BotCommandScopeAllPrivateChats](https://core.telegram.org/bots/api#botcommandscopeallprivatechats); -   [BotCommandScopeAllGroupChats](https://core.telegram.org/bots/api#botcommandscopeallgroupchats); -   [BotCommandScopeAllChatAdministrators](https://core.telegram.org/bots/api#botcommandscopeallchatadministrators); -   [BotCommandScopeChat](https://core.telegram.org/bots/api#botcommandscopechat); -   [BotCommandScopeChatAdministrators](https://core.telegram.org/bots/api#botcommandscopechatadministrators); -   [BotCommandScopeChatMember](https://core.telegram.org/bots/api#botcommandscopechatmember)')]
    case BotCommandScope = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeTypeDTO::class;
    #[Description('Represents the default [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands. Default commands are used if no commands with a [narrower scope](https://core.telegram.org/bots/api#determining-list-of-commands) are specified for the user.')]
    case BotCommandScopeDefault = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeDefaultTypeDTO::class;
    #[Description('Represents the [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands, covering all private chats.')]
    case BotCommandScopeAllPrivateChats = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeAllPrivateChatsTypeDTO::class;
    #[Description('Represents the [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands, covering all group and supergroup chats.')]
    case BotCommandScopeAllGroupChats = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeAllGroupChatsTypeDTO::class;
    #[Description('Represents the [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands, covering all group and supergroup chat administrators.')]
    case BotCommandScopeAllChatAdministrators = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeAllChatAdministratorsTypeDTO::class;
    #[Description('Represents the [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands, covering a specific chat.')]
    case BotCommandScopeChat = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeChatTypeDTO::class;
    #[Description('Represents the [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands, covering all administrators of a specific group or supergroup chat.')]
    case BotCommandScopeChatAdministrators = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeChatAdministratorsTypeDTO::class;
    #[Description('Represents the [scope](https://core.telegram.org/bots/api#botcommandscope) of bot commands, covering a specific member of a group or supergroup chat.')]
    case BotCommandScopeChatMember = \BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeChatMemberTypeDTO::class;
    #[Description('This object represents the bot"s name.')]
    case BotName = \BAGArt\TelegramBot\TgApi\Types\DTO\BotNameTypeDTO::class;
    #[Description('This object represents the bot"s description.')]
    case BotDescription = \BAGArt\TelegramBot\TgApi\Types\DTO\BotDescriptionTypeDTO::class;
    #[Description('This object represents the bot"s short description.')]
    case BotShortDescription = \BAGArt\TelegramBot\TgApi\Types\DTO\BotShortDescriptionTypeDTO::class;
    #[Description('This object describes the bot"s menu button in a private chat. It should be one of; ; -   [MenuButtonCommands](https://core.telegram.org/bots/api#menubuttoncommands); -   [MenuButtonWebApp](https://core.telegram.org/bots/api#menubuttonwebapp); -   [MenuButtonDefault](https://core.telegram.org/bots/api#menubuttondefault); ; If a menu button other than [MenuButtonDefault](https://core.telegram.org/bots/api#menubuttondefault) is set for a private chat, then it is applied in the chat. Otherwise the default menu button is applied. By default, the menu button opens the list of bot commands.')]
    case MenuButton = \BAGArt\TelegramBot\TgApi\Types\DTO\MenuButtonTypeDTO::class;
    #[Description('Represents a menu button, which opens the bot"s list of commands.')]
    case MenuButtonCommands = \BAGArt\TelegramBot\TgApi\Types\DTO\MenuButtonCommandsTypeDTO::class;
    #[Description('Represents a menu button, which launches a [Web App](https://core.telegram.org/bots/webapps).')]
    case MenuButtonWebApp = \BAGArt\TelegramBot\TgApi\Types\DTO\MenuButtonWebAppTypeDTO::class;
    #[Description('Describes that no specific value for the menu button was set.')]
    case MenuButtonDefault = \BAGArt\TelegramBot\TgApi\Types\DTO\MenuButtonDefaultTypeDTO::class;
    #[Description('This object describes the source of a chat boost. It can be one of; ; -   [ChatBoostSourcePremium](https://core.telegram.org/bots/api#chatboostsourcepremium); -   [ChatBoostSourceGiftCode](https://core.telegram.org/bots/api#chatboostsourcegiftcode); -   [ChatBoostSourceGiveaway](https://core.telegram.org/bots/api#chatboostsourcegiveaway)')]
    case ChatBoostSource = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostSourceTypeDTO::class;
    #[Description('The boost was obtained by subscribing to Telegram Premium or by gifting a Telegram Premium subscription to another user.')]
    case ChatBoostSourcePremium = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostSourcePremiumTypeDTO::class;
    #[Description('The boost was obtained by the creation of Telegram Premium gift codes to boost a chat. Each such code boosts the chat 4 times for the duration of the corresponding Telegram Premium subscription.')]
    case ChatBoostSourceGiftCode = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostSourceGiftCodeTypeDTO::class;
    #[Description('The boost was obtained by the creation of a Telegram Premium or a Telegram Star giveaway. This boosts the chat 4 times for the duration of the corresponding Telegram Premium subscription for Telegram Premium giveaways and _prize\_star\_count_ / 500 times for one year for Telegram Star giveaways.')]
    case ChatBoostSourceGiveaway = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostSourceGiveawayTypeDTO::class;
    #[Description('This object contains information about a chat boost.')]
    case ChatBoost = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostTypeDTO::class;
    #[Description('This object represents a boost added to a chat or changed.')]
    case ChatBoostUpdated = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostUpdatedTypeDTO::class;
    #[Description('This object represents a boost removed from a chat.')]
    case ChatBoostRemoved = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatBoostRemovedTypeDTO::class;
    #[Description('Describes a service message about the chat owner leaving the chat.')]
    case ChatOwnerLeft = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatOwnerLeftTypeDTO::class;
    #[Description('Describes a service message about an ownership change in the chat.')]
    case ChatOwnerChanged = \BAGArt\TelegramBot\TgApi\Types\DTO\ChatOwnerChangedTypeDTO::class;
    #[Description('This object represents a list of boosts added to a chat by a user.')]
    case UserChatBoosts = \BAGArt\TelegramBot\TgApi\Types\DTO\UserChatBoostsTypeDTO::class;
    #[Description('Represents the rights of a business bot.')]
    case BusinessBotRights = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessBotRightsTypeDTO::class;
    #[Description('Describes the connection of the bot with a business account.')]
    case BusinessConnection = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessConnectionTypeDTO::class;
    #[Description('This object is received when messages are deleted from a connected business account.')]
    case BusinessMessagesDeleted = \BAGArt\TelegramBot\TgApi\Types\DTO\BusinessMessagesDeletedTypeDTO::class;
    #[Description('Describes why a request was unsuccessful.')]
    case ResponseParameters = \BAGArt\TelegramBot\TgApi\Types\DTO\ResponseParametersTypeDTO::class;
    #[Description('This object represents the content of a media message to be sent. It should be one of; ; -   [InputMediaAnimation](https://core.telegram.org/bots/api#inputmediaanimation); -   [InputMediaDocument](https://core.telegram.org/bots/api#inputmediadocument); -   [InputMediaAudio](https://core.telegram.org/bots/api#inputmediaaudio); -   [InputMediaPhoto](https://core.telegram.org/bots/api#inputmediaphoto); -   [InputMediaVideo](https://core.telegram.org/bots/api#inputmediavideo)')]
    case InputMedia = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMediaTypeDTO::class;
    #[Description('Represents a photo to be sent.')]
    case InputMediaPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMediaPhotoTypeDTO::class;
    #[Description('Represents a video to be sent.')]
    case InputMediaVideo = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMediaVideoTypeDTO::class;
    #[Description('Represents an animation file (GIF or H.264/MPEG-4 AVC video without sound) to be sent.')]
    case InputMediaAnimation = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMediaAnimationTypeDTO::class;
    #[Description('Represents an audio file to be treated as music to be sent.')]
    case InputMediaAudio = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMediaAudioTypeDTO::class;
    #[Description('Represents a general file to be sent.')]
    case InputMediaDocument = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMediaDocumentTypeDTO::class;
    #[Description('This object describes the paid media to be sent. Currently, it can be one of; ; -   [InputPaidMediaPhoto](https://core.telegram.org/bots/api#inputpaidmediaphoto); -   [InputPaidMediaVideo](https://core.telegram.org/bots/api#inputpaidmediavideo)')]
    case InputPaidMedia = \BAGArt\TelegramBot\TgApi\Types\DTO\InputPaidMediaTypeDTO::class;
    #[Description('The paid media to send is a photo.')]
    case InputPaidMediaPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\InputPaidMediaPhotoTypeDTO::class;
    #[Description('The paid media to send is a video.')]
    case InputPaidMediaVideo = \BAGArt\TelegramBot\TgApi\Types\DTO\InputPaidMediaVideoTypeDTO::class;
    #[Description('This object describes a profile photo to set. Currently, it can be one of; ; -   [InputProfilePhotoStatic](https://core.telegram.org/bots/api#inputprofilephotostatic); -   [InputProfilePhotoAnimated](https://core.telegram.org/bots/api#inputprofilephotoanimated)')]
    case InputProfilePhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\InputProfilePhotoTypeDTO::class;
    #[Description('A static profile photo in the .JPG format.')]
    case InputProfilePhotoStatic = \BAGArt\TelegramBot\TgApi\Types\DTO\InputProfilePhotoStaticTypeDTO::class;
    #[Description('An animated profile photo in the MPEG4 format.')]
    case InputProfilePhotoAnimated = \BAGArt\TelegramBot\TgApi\Types\DTO\InputProfilePhotoAnimatedTypeDTO::class;
    #[Description('This object describes the content of a story to post. Currently, it can be one of; ; -   [InputStoryContentPhoto](https://core.telegram.org/bots/api#inputstorycontentphoto); -   [InputStoryContentVideo](https://core.telegram.org/bots/api#inputstorycontentvideo)')]
    case InputStoryContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputStoryContentTypeDTO::class;
    #[Description('Describes a photo to post as a story.')]
    case InputStoryContentPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\InputStoryContentPhotoTypeDTO::class;
    #[Description('Describes a video to post as a story.')]
    case InputStoryContentVideo = \BAGArt\TelegramBot\TgApi\Types\DTO\InputStoryContentVideoTypeDTO::class;
    #[Description('This object represents a sticker.')]
    case Sticker = \BAGArt\TelegramBot\TgApi\Types\DTO\StickerTypeDTO::class;
    #[Description('This object represents a sticker set.')]
    case StickerSet = \BAGArt\TelegramBot\TgApi\Types\DTO\StickerSetTypeDTO::class;
    #[Description('This object describes the position on faces where a mask should be placed by default.')]
    case MaskPosition = \BAGArt\TelegramBot\TgApi\Types\DTO\MaskPositionTypeDTO::class;
    #[Description('This object describes a sticker to be added to a sticker set.')]
    case InputSticker = \BAGArt\TelegramBot\TgApi\Types\DTO\InputStickerTypeDTO::class;
    #[Description('This object represents an incoming inline query. When the user sends an empty query, your bot could return some default or trending results.')]
    case InlineQuery = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryTypeDTO::class;
    #[Description('This object represents a button to be shown above inline query results. You **must** use exactly one of the optional fields.')]
    case InlineQueryResultsButton = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultsButtonTypeDTO::class;
    #[Description('This object represents one result of an inline query. Telegram clients currently support results of the following 20 types:; ; -   [InlineQueryResultCachedAudio](https://core.telegram.org/bots/api#inlinequeryresultcachedaudio); -   [InlineQueryResultCachedDocument](https://core.telegram.org/bots/api#inlinequeryresultcacheddocument); -   [InlineQueryResultCachedGif](https://core.telegram.org/bots/api#inlinequeryresultcachedgif); -   [InlineQueryResultCachedMpeg4Gif](https://core.telegram.org/bots/api#inlinequeryresultcachedmpeg4gif); -   [InlineQueryResultCachedPhoto](https://core.telegram.org/bots/api#inlinequeryresultcachedphoto); -   [InlineQueryResultCachedSticker](https://core.telegram.org/bots/api#inlinequeryresultcachedsticker); -   [InlineQueryResultCachedVideo](https://core.telegram.org/bots/api#inlinequeryresultcachedvideo); -   [InlineQueryResultCachedVoice](https://core.telegram.org/bots/api#inlinequeryresultcachedvoice); -   [InlineQueryResultArticle](https://core.telegram.org/bots/api#inlinequeryresultarticle); -   [InlineQueryResultAudio](https://core.telegram.org/bots/api#inlinequeryresultaudio); -   [InlineQueryResultContact](https://core.telegram.org/bots/api#inlinequeryresultcontact); -   [InlineQueryResultGame](https://core.telegram.org/bots/api#inlinequeryresultgame); -   [InlineQueryResultDocument](https://core.telegram.org/bots/api#inlinequeryresultdocument); -   [InlineQueryResultGif](https://core.telegram.org/bots/api#inlinequeryresultgif); -   [InlineQueryResultLocation](https://core.telegram.org/bots/api#inlinequeryresultlocation); -   [InlineQueryResultMpeg4Gif](https://core.telegram.org/bots/api#inlinequeryresultmpeg4gif); -   [InlineQueryResultPhoto](https://core.telegram.org/bots/api#inlinequeryresultphoto); -   [InlineQueryResultVenue](https://core.telegram.org/bots/api#inlinequeryresultvenue); -   [InlineQueryResultVideo](https://core.telegram.org/bots/api#inlinequeryresultvideo); -   [InlineQueryResultVoice](https://core.telegram.org/bots/api#inlinequeryresultvoice); ; **Note:** All URLs passed in inline query results will be available to end users and therefore must be assumed to be **public**.')]
    case InlineQueryResult = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultTypeDTO::class;
    #[Description('Represents a link to an article or web page.')]
    case InlineQueryResultArticle = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultArticleTypeDTO::class;
    #[Description('Represents a link to a photo. By default, this photo will be sent by the user with optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the photo.')]
    case InlineQueryResultPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultPhotoTypeDTO::class;
    #[Description('Represents a link to an animated GIF file. By default, this animated GIF file will be sent by the user with optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the animation.')]
    case InlineQueryResultGif = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultGifTypeDTO::class;
    #[Description('Represents a link to a video animation (H.264/MPEG-4 AVC video without sound). By default, this animated MPEG-4 file will be sent by the user with optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the animation.')]
    case InlineQueryResultMpeg4Gif = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultMpeg4GifTypeDTO::class;
    #[Description('Represents a link to a page containing an embedded video player or a video file. By default, this video file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the video.; ; > If an InlineQueryResultVideo message contains an embedded video (e.g., YouTube), you **must** replace its content using _input\_message\_content_.')]
    case InlineQueryResultVideo = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultVideoTypeDTO::class;
    #[Description('Represents a link to an MP3 audio file. By default, this audio file will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the audio.')]
    case InlineQueryResultAudio = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultAudioTypeDTO::class;
    #[Description('Represents a link to a voice recording in an .OGG container encoded with OPUS. By default, this voice recording will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the the voice message.')]
    case InlineQueryResultVoice = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultVoiceTypeDTO::class;
    #[Description('Represents a link to a file. By default, this file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the file. Currently, only **.PDF** and **.ZIP** files can be sent using this method.')]
    case InlineQueryResultDocument = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultDocumentTypeDTO::class;
    #[Description('Represents a location on a map. By default, the location will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the location.')]
    case InlineQueryResultLocation = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultLocationTypeDTO::class;
    #[Description('Represents a venue. By default, the venue will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the venue.')]
    case InlineQueryResultVenue = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultVenueTypeDTO::class;
    #[Description('Represents a contact with a phone number. By default, this contact will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the contact.')]
    case InlineQueryResultContact = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultContactTypeDTO::class;
    #[Description('Represents a [Game](https://core.telegram.org/bots/api#games).')]
    case InlineQueryResultGame = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultGameTypeDTO::class;
    #[Description('Represents a link to a photo stored on the Telegram servers. By default, this photo will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the photo.')]
    case InlineQueryResultCachedPhoto = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedPhotoTypeDTO::class;
    #[Description('Represents a link to an animated GIF file stored on the Telegram servers. By default, this animated GIF file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with specified content instead of the animation.')]
    case InlineQueryResultCachedGif = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedGifTypeDTO::class;
    #[Description('Represents a link to a video animation (H.264/MPEG-4 AVC video without sound) stored on the Telegram servers. By default, this animated MPEG-4 file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the animation.')]
    case InlineQueryResultCachedMpeg4Gif = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedMpeg4GifTypeDTO::class;
    #[Description('Represents a link to a sticker stored on the Telegram servers. By default, this sticker will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the sticker.')]
    case InlineQueryResultCachedSticker = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedStickerTypeDTO::class;
    #[Description('Represents a link to a file stored on the Telegram servers. By default, this file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the file.')]
    case InlineQueryResultCachedDocument = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedDocumentTypeDTO::class;
    #[Description('Represents a link to a video file stored on the Telegram servers. By default, this video file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the video.')]
    case InlineQueryResultCachedVideo = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedVideoTypeDTO::class;
    #[Description('Represents a link to a voice message stored on the Telegram servers. By default, this voice message will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the voice message.')]
    case InlineQueryResultCachedVoice = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedVoiceTypeDTO::class;
    #[Description('Represents a link to an MP3 audio file stored on the Telegram servers. By default, this audio file will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the audio.')]
    case InlineQueryResultCachedAudio = \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultCachedAudioTypeDTO::class;
    #[Description('This object represents the content of a message to be sent as a result of an inline query. Telegram clients currently support the following 5 types:; ; -   [InputTextMessageContent](https://core.telegram.org/bots/api#inputtextmessagecontent); -   [InputLocationMessageContent](https://core.telegram.org/bots/api#inputlocationmessagecontent); -   [InputVenueMessageContent](https://core.telegram.org/bots/api#inputvenuemessagecontent); -   [InputContactMessageContent](https://core.telegram.org/bots/api#inputcontactmessagecontent); -   [InputInvoiceMessageContent](https://core.telegram.org/bots/api#inputinvoicemessagecontent)')]
    case InputMessageContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputMessageContentTypeDTO::class;
    #[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a text message to be sent as the result of an inline query.')]
    case InputTextMessageContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputTextMessageContentTypeDTO::class;
    #[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a location message to be sent as the result of an inline query.')]
    case InputLocationMessageContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputLocationMessageContentTypeDTO::class;
    #[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a venue message to be sent as the result of an inline query.')]
    case InputVenueMessageContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputVenueMessageContentTypeDTO::class;
    #[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of a contact message to be sent as the result of an inline query.')]
    case InputContactMessageContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputContactMessageContentTypeDTO::class;
    #[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of an invoice message to be sent as the result of an inline query.')]
    case InputInvoiceMessageContent = \BAGArt\TelegramBot\TgApi\Types\DTO\InputInvoiceMessageContentTypeDTO::class;
    #[Description('Represents a [result](https://core.telegram.org/bots/api#inlinequeryresult) of an inline query that was chosen by the user and sent to their chat partner.; ; **Note:** It is necessary to enable [inline feedback](https://core.telegram.org/bots/inline#collecting-feedback) via [@BotFather](https://t.me/botfather) in order to receive these objects in updates.')]
    case ChosenInlineResult = \BAGArt\TelegramBot\TgApi\Types\DTO\ChosenInlineResultTypeDTO::class;
    #[Description('Describes an inline message sent by a [Web App](https://core.telegram.org/bots/webapps) on behalf of a user.')]
    case SentWebAppMessage = \BAGArt\TelegramBot\TgApi\Types\DTO\SentWebAppMessageTypeDTO::class;
    #[Description('Describes an inline message to be sent by a user of a Mini App.')]
    case PreparedInlineMessage = \BAGArt\TelegramBot\TgApi\Types\DTO\PreparedInlineMessageTypeDTO::class;
    #[Description('This object represents a portion of the price for goods or services.')]
    case LabeledPrice = \BAGArt\TelegramBot\TgApi\Types\DTO\LabeledPriceTypeDTO::class;
    #[Description('This object contains basic information about an invoice.')]
    case Invoice = \BAGArt\TelegramBot\TgApi\Types\DTO\InvoiceTypeDTO::class;
    #[Description('This object represents a shipping address.')]
    case ShippingAddress = \BAGArt\TelegramBot\TgApi\Types\DTO\ShippingAddressTypeDTO::class;
    #[Description('This object represents information about an order.')]
    case OrderInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\OrderInfoTypeDTO::class;
    #[Description('This object represents one shipping option.')]
    case ShippingOption = \BAGArt\TelegramBot\TgApi\Types\DTO\ShippingOptionTypeDTO::class;
    #[Description('This object contains basic information about a successful payment. Note that if the buyer initiates a chargeback with the relevant payment provider following this transaction, the funds may be debited from your balance. This is outside of Telegram"s control.')]
    case SuccessfulPayment = \BAGArt\TelegramBot\TgApi\Types\DTO\SuccessfulPaymentTypeDTO::class;
    #[Description('This object contains basic information about a refunded payment.')]
    case RefundedPayment = \BAGArt\TelegramBot\TgApi\Types\DTO\RefundedPaymentTypeDTO::class;
    #[Description('This object contains information about an incoming shipping query.')]
    case ShippingQuery = \BAGArt\TelegramBot\TgApi\Types\DTO\ShippingQueryTypeDTO::class;
    #[Description('This object contains information about an incoming pre-checkout query.')]
    case PreCheckoutQuery = \BAGArt\TelegramBot\TgApi\Types\DTO\PreCheckoutQueryTypeDTO::class;
    #[Description('This object contains information about a paid media purchase.')]
    case PaidMediaPurchased = \BAGArt\TelegramBot\TgApi\Types\DTO\PaidMediaPurchasedTypeDTO::class;
    #[Description('This object describes the state of a revenue withdrawal operation. Currently, it can be one of; ; -   [RevenueWithdrawalStatePending](https://core.telegram.org/bots/api#revenuewithdrawalstatepending); -   [RevenueWithdrawalStateSucceeded](https://core.telegram.org/bots/api#revenuewithdrawalstatesucceeded); -   [RevenueWithdrawalStateFailed](https://core.telegram.org/bots/api#revenuewithdrawalstatefailed)')]
    case RevenueWithdrawalState = \BAGArt\TelegramBot\TgApi\Types\DTO\RevenueWithdrawalStateTypeDTO::class;
    #[Description('The withdrawal is in progress.')]
    case RevenueWithdrawalStatePending = \BAGArt\TelegramBot\TgApi\Types\DTO\RevenueWithdrawalStatePendingTypeDTO::class;
    #[Description('The withdrawal succeeded.')]
    case RevenueWithdrawalStateSucceeded = \BAGArt\TelegramBot\TgApi\Types\DTO\RevenueWithdrawalStateSucceededTypeDTO::class;
    #[Description('The withdrawal failed and the transaction was refunded.')]
    case RevenueWithdrawalStateFailed = \BAGArt\TelegramBot\TgApi\Types\DTO\RevenueWithdrawalStateFailedTypeDTO::class;
    #[Description('Contains information about the affiliate that received a commission via this transaction.')]
    case AffiliateInfo = \BAGArt\TelegramBot\TgApi\Types\DTO\AffiliateInfoTypeDTO::class;
    #[Description('This object describes the source of a transaction, or its recipient for outgoing transactions. Currently, it can be one of; ; -   [TransactionPartnerUser](https://core.telegram.org/bots/api#transactionpartneruser); -   [TransactionPartnerChat](https://core.telegram.org/bots/api#transactionpartnerchat); -   [TransactionPartnerAffiliateProgram](https://core.telegram.org/bots/api#transactionpartneraffiliateprogram); -   [TransactionPartnerFragment](https://core.telegram.org/bots/api#transactionpartnerfragment); -   [TransactionPartnerTelegramAds](https://core.telegram.org/bots/api#transactionpartnertelegramads); -   [TransactionPartnerTelegramApi](https://core.telegram.org/bots/api#transactionpartnertelegramapi); -   [TransactionPartnerOther](https://core.telegram.org/bots/api#transactionpartnerother)')]
    case TransactionPartner = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerTypeDTO::class;
    #[Description('Describes a transaction with a user.')]
    case TransactionPartnerUser = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerUserTypeDTO::class;
    #[Description('Describes a transaction with a chat.')]
    case TransactionPartnerChat = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerChatTypeDTO::class;
    #[Description('Describes the affiliate program that issued the affiliate commission received via this transaction.')]
    case TransactionPartnerAffiliateProgram = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerAffiliateProgramTypeDTO::class;
    #[Description('Describes a withdrawal transaction with Fragment.')]
    case TransactionPartnerFragment = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerFragmentTypeDTO::class;
    #[Description('Describes a withdrawal transaction to the Telegram Ads platform.')]
    case TransactionPartnerTelegramAds = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerTelegramAdsTypeDTO::class;
    #[Description('Describes a transaction with payment for [paid broadcasting](https://core.telegram.org/bots/api#paid-broadcasts).')]
    case TransactionPartnerTelegramApi = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerTelegramApiTypeDTO::class;
    #[Description('Describes a transaction with an unknown source or recipient.')]
    case TransactionPartnerOther = \BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerOtherTypeDTO::class;
    #[Description('Describes a Telegram Star transaction. Note that if the buyer initiates a chargeback with the payment provider from whom they acquired Stars (e.g., Apple, Google) following this transaction, the refunded Stars will be deducted from the bot"s balance. This is outside of Telegram"s control.')]
    case StarTransaction = \BAGArt\TelegramBot\TgApi\Types\DTO\StarTransactionTypeDTO::class;
    #[Description('Contains a list of Telegram Star transactions.')]
    case StarTransactions = \BAGArt\TelegramBot\TgApi\Types\DTO\StarTransactionsTypeDTO::class;
    #[Description('Describes Telegram Passport data shared with the bot by the user.')]
    case PassportData = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportDataTypeDTO::class;
    #[Description('This object represents a file uploaded to Telegram Passport. Currently all Telegram Passport files are in JPEG format when decrypted and don"t exceed 10MB.')]
    case PassportFile = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportFileTypeDTO::class;
    #[Description('Describes documents or other Telegram Passport elements shared with the bot by the user.')]
    case EncryptedPassportElement = \BAGArt\TelegramBot\TgApi\Types\DTO\EncryptedPassportElementTypeDTO::class;
    #[Description('Describes data required for decrypting and authenticating [EncryptedPassportElement](https://core.telegram.org/bots/api#encryptedpassportelement). See the [Telegram Passport Documentation](https://core.telegram.org/passport#receiving-information) for a complete description of the data decryption and authentication processes.')]
    case EncryptedCredentials = \BAGArt\TelegramBot\TgApi\Types\DTO\EncryptedCredentialsTypeDTO::class;
    #[Description('This object represents an error in the Telegram Passport element which was submitted that should be resolved by the user. It should be one of:; ; -   [PassportElementErrorDataField](https://core.telegram.org/bots/api#passportelementerrordatafield); -   [PassportElementErrorFrontSide](https://core.telegram.org/bots/api#passportelementerrorfrontside); -   [PassportElementErrorReverseSide](https://core.telegram.org/bots/api#passportelementerrorreverseside); -   [PassportElementErrorSelfie](https://core.telegram.org/bots/api#passportelementerrorselfie); -   [PassportElementErrorFile](https://core.telegram.org/bots/api#passportelementerrorfile); -   [PassportElementErrorFiles](https://core.telegram.org/bots/api#passportelementerrorfiles); -   [PassportElementErrorTranslationFile](https://core.telegram.org/bots/api#passportelementerrortranslationfile); -   [PassportElementErrorTranslationFiles](https://core.telegram.org/bots/api#passportelementerrortranslationfiles); -   [PassportElementErrorUnspecified](https://core.telegram.org/bots/api#passportelementerrorunspecified)')]
    case PassportElementError = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorTypeDTO::class;
    #[Description('Represents an issue in one of the data fields that was provided by the user. The error is considered resolved when the field"s value changes.')]
    case PassportElementErrorDataField = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorDataFieldTypeDTO::class;
    #[Description('Represents an issue with the front side of a document. The error is considered resolved when the file with the front side of the document changes.')]
    case PassportElementErrorFrontSide = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorFrontSideTypeDTO::class;
    #[Description('Represents an issue with the reverse side of a document. The error is considered resolved when the file with reverse side of the document changes.')]
    case PassportElementErrorReverseSide = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorReverseSideTypeDTO::class;
    #[Description('Represents an issue with the selfie with a document. The error is considered resolved when the file with the selfie changes.')]
    case PassportElementErrorSelfie = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorSelfieTypeDTO::class;
    #[Description('Represents an issue with a document scan. The error is considered resolved when the file with the document scan changes.')]
    case PassportElementErrorFile = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorFileTypeDTO::class;
    #[Description('Represents an issue with a list of scans. The error is considered resolved when the list of files containing the scans changes.')]
    case PassportElementErrorFiles = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorFilesTypeDTO::class;
    #[Description('Represents an issue with one of the files that constitute the translation of a document. The error is considered resolved when the file changes.')]
    case PassportElementErrorTranslationFile = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorTranslationFileTypeDTO::class;
    #[Description('Represents an issue with the translated version of a document. The error is considered resolved when a file with the document translation change.')]
    case PassportElementErrorTranslationFiles = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorTranslationFilesTypeDTO::class;
    #[Description('Represents an issue in an unspecified place. The error is considered resolved when new data is added.')]
    case PassportElementErrorUnspecified = \BAGArt\TelegramBot\TgApi\Types\DTO\PassportElementErrorUnspecifiedTypeDTO::class;
    #[Description('This object represents a game. Use BotFather to create and edit games, their short names will act as unique identifiers.')]
    case Game = \BAGArt\TelegramBot\TgApi\Types\DTO\GameTypeDTO::class;
    #[Description('A placeholder, currently holds no information. Use [BotFather](https://t.me/botfather) to set up your game.')]
    case CallbackGame = \BAGArt\TelegramBot\TgApi\Types\DTO\CallbackGameTypeDTO::class;
    #[Description('This object represents one row of the high scores table for a game.')]
    case GameHighScore = \BAGArt\TelegramBot\TgApi\Types\DTO\GameHighScoreTypeDTO::class;
}
