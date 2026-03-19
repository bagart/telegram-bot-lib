<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\StyleEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents one button of an inline keyboard. Exactly one of the fields other than _text_, _icon\_custom\_emoji\_id_, and _style_ must be used to specify the type of the button.')]
#[See('https://core.telegram.org/bots/api#inlinekeyboardbutton')]
class InlineKeyboardButtonTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Label text on the button')]
        public string $text,
        #[Description('Unique identifier of the custom emoji shown before the text of the button. Can only be used by bots that purchased additional usernames on [Fragment](https://fragment.com/) or in the messages directly sent by the bot to private, group and supergroup chats if the owner of the bot has a Telegram Premium subscription.')]
        public ?string $iconCustomEmojiId = null,
        #[Description('Style of the button. Must be one of “danger” (red), “success” (green) or “primary” (blue). If omitted, then an app-specific style is used.')]
        public ?StyleEnum $style = null,
        #[Description('HTTP or tg:// URL to be opened when the button is pressed. Links `tg://user?id=<user_id>` can be used to mention a user by their identifier without using a username, if this is allowed by their privacy settings.')]
        public ?string $url = null,
        #[Description('Data to be sent in a [callback query](https://core.telegram.org/bots/api#callbackquery) to the bot when the button is pressed, 1-64 bytes')]
        public ?string $callbackData = null,
        #[Description('Description of the [Web App](https://core.telegram.org/bots/webapps) that will be launched when the user presses the button. The Web App will be able to send an arbitrary message on behalf of the user using the method [answerWebAppQuery](https://core.telegram.org/bots/api#answerwebappquery). Available only in private chats between a user and the bot. Not supported for messages sent on behalf of a Telegram Business account.')]
        public ?WebAppInfoTypeDTO $webApp = null,
        #[Description('An HTTPS URL used to automatically authorize the user. Can be used as a replacement for the [Telegram Login Widget](https://core.telegram.org/widgets/login).')]
        public ?LoginUrlTypeDTO $loginUrl = null,
        #[Description('If set, pressing the button will prompt the user to select one of their chats, open that chat and insert the bot"s username and the specified inline query in the input field. May be empty, in which case just the bot"s username will be inserted. Not supported for messages sent in channel direct messages chats and on behalf of a Telegram Business account.')]
        public ?string $switchInlineQuery = null,
        #[Description('If set, pressing the button will insert the bot"s username and the specified inline query in the current chat"s input field. May be empty, in which case only the bot"s username will be inserted.; ; This offers a quick way for the user to open your bot in inline mode in the same chat - good for selecting something from multiple options. Not supported in channels and for messages sent in channel direct messages chats and on behalf of a Telegram Business account.')]
        public ?string $switchInlineQueryCurrentChat = null,
        #[Description('If set, pressing the button will prompt the user to select one of their chats of the specified type, open that chat and insert the bot"s username and the specified inline query in the input field. Not supported for messages sent in channel direct messages chats and on behalf of a Telegram Business account.')]
        public ?SwitchInlineQueryChosenChatTypeDTO $switchInlineQueryChosenChat = null,
        #[Description('Description of the button that copies the specified text to the clipboard.')]
        public ?CopyTextButtonTypeDTO $copyText = null,
        #[Description('Description of the game that will be launched when the user presses the button.; ; **NOTE:** This type of button **must** always be the first button in the first row.')]
        public ?CallbackGameTypeDTO $callbackGame = null,
        #[Description('Specify _True_, to send a [Pay button](https://core.telegram.org/bots/api#payments). Substrings “![⭐](//telegram.org/img/emoji/40/E2AD90.png)” and “XTR” in the buttons"s text will be replaced with a Telegram Star icon.; ; **NOTE:** This type of button **must** always be the first button in the first row and can only be used in invoice messages.')]
        public ?bool $pay = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineKeyboardButton;
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
{"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"icon_custom_emoji_id":{"property":"iconCustomEmojiId","tgPropName":"icon_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"style":{"property":"style","tgPropName":"style","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\StyleEnum"],"tgTypes":[{"type":"str","literal":"danger"},{"type":"str","literal":"success"},{"type":"str","literal":"primary"}],"nullable":true,"required":false},"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"callback_data":{"property":"callbackData","tgPropName":"callback_data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"web_app":{"property":"webApp","tgPropName":"web_app","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\WebAppInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"WebAppInfo"}],"nullable":true,"required":false},"login_url":{"property":"loginUrl","tgPropName":"login_url","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LoginUrlTypeDTO"],"tgTypes":[{"type":"api-type","name":"LoginUrl"}],"nullable":true,"required":false},"switch_inline_query":{"property":"switchInlineQuery","tgPropName":"switch_inline_query","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"switch_inline_query_current_chat":{"property":"switchInlineQueryCurrentChat","tgPropName":"switch_inline_query_current_chat","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"switch_inline_query_chosen_chat":{"property":"switchInlineQueryChosenChat","tgPropName":"switch_inline_query_chosen_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SwitchInlineQueryChosenChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"SwitchInlineQueryChosenChat"}],"nullable":true,"required":false},"copy_text":{"property":"copyText","tgPropName":"copy_text","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\CopyTextButtonTypeDTO"],"tgTypes":[{"type":"api-type","name":"CopyTextButton"}],"nullable":true,"required":false},"callback_game":{"property":"callbackGame","tgPropName":"callback_game","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\CallbackGameTypeDTO"],"tgTypes":[{"type":"api-type","name":"CallbackGame"}],"nullable":true,"required":false},"pay":{"property":"pay","tgPropName":"pay","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
