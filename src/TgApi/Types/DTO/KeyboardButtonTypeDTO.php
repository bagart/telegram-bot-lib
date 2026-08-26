<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents one button of the reply keyboard. At most one of the fields other than _text_, _icon\_custom\_emoji\_id_, and _style_ must be used to specify the type of the button. For simple text buttons, _String_ can be used instead of this object to specify the button text.')]
#[See('https://core.telegram.org/bots/api#keyboardbutton')]
class KeyboardButtonTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Text of the button. If none of the fields other than _text_, _icon\_custom\_emoji\_id_, and _style_ are used, it will be sent as a message when the button is pressed')]
        public string $text,
        #[Description('Unique identifier of the custom emoji shown before the text of the button. Can only be used by bots that purchased additional usernames on [Fragment](https://fragment.com/) or in the messages directly sent by the bot to private, group and supergroup chats if the owner of the bot has a Telegram Premium subscription.')]
        public ?string $iconCustomEmojiId = null,
        #[Description('Style of the button. Must be one of “danger” (red), “success” (green) or “primary” (blue). If omitted, then an app-specific style is used.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\Enum\StyleEnum $style = null,
        #[Description('If specified, pressing the button will open a list of suitable users. Identifiers of selected users will be sent to the bot in a “users\_shared” service message. Available in private chats only.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonRequestUsersTypeDTO $requestUsers = null,
        #[Description('If specified, pressing the button will open a list of suitable chats. Tapping on a chat will send its identifier to the bot in a “chat\_shared” service message. Available in private chats only.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonRequestChatTypeDTO $requestChat = null,
        #[Description('If _True_, the user"s phone number will be sent as a contact when the button is pressed. Available in private chats only.')]
        public ?bool $requestContact = null,
        #[Description('If _True_, the user"s current location will be sent when the button is pressed. Available in private chats only.')]
        public ?bool $requestLocation = null,
        #[Description('If specified, the user will be asked to create a poll and send it to the bot when the button is pressed. Available in private chats only.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\KeyboardButtonPollTypeTypeDTO $requestPoll = null,
        #[Description('If specified, the described [Web App](https://core.telegram.org/bots/webapps) will be launched when the button is pressed. The Web App will be able to send a “web\_app\_data” service message. Available in private chats only.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\WebAppInfoTypeDTO $webApp = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::KeyboardButton;
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
{"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"icon_custom_emoji_id":{"property":"iconCustomEmojiId","tgPropName":"icon_custom_emoji_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"style":{"property":"style","tgPropName":"style","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\StyleEnum"],"tgTypes":[{"type":"str","literal":"danger"},{"type":"str","literal":"success"},{"type":"str","literal":"primary"}],"nullable":true,"required":false},"request_users":{"property":"requestUsers","tgPropName":"request_users","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\KeyboardButtonRequestUsersTypeDTO"],"tgTypes":[{"type":"api-type","name":"KeyboardButtonRequestUsers"}],"nullable":true,"required":false},"request_chat":{"property":"requestChat","tgPropName":"request_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\KeyboardButtonRequestChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"KeyboardButtonRequestChat"}],"nullable":true,"required":false},"request_contact":{"property":"requestContact","tgPropName":"request_contact","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_location":{"property":"requestLocation","tgPropName":"request_location","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_poll":{"property":"requestPoll","tgPropName":"request_poll","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\KeyboardButtonPollTypeTypeDTO"],"tgTypes":[{"type":"api-type","name":"KeyboardButtonPollType"}],"nullable":true,"required":false},"web_app":{"property":"webApp","tgPropName":"web_app","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\WebAppInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"WebAppInfo"}],"nullable":true,"required":false}}
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
