<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a [custom keyboard](https://core.telegram.org/bots/features#keyboards) with reply options (see [Introduction to bots](https://core.telegram.org/bots/features#keyboards) for details and examples). Not supported in channels and for messages sent on behalf of a Telegram Business account.')]
#[See('https://core.telegram.org/bots/api#replykeyboardmarkup')]
class ReplyKeyboardMarkupTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Array of button rows, each represented by an Array of [KeyboardButton](https://core.telegram.org/bots/api#keyboardbutton) objects')]
        public array $keyboard,
        #[Description('Requests clients to always show the keyboard when the regular keyboard is hidden. Defaults to _false_, in which case the custom keyboard can be hidden and opened with a keyboard icon.')]
        public ?bool $isPersistent = null,
        #[Description('Requests clients to resize the keyboard vertically for optimal fit (e.g., make the keyboard smaller if there are just two rows of buttons). Defaults to _false_, in which case the custom keyboard is always of the same height as the app"s standard keyboard.')]
        public ?bool $resizeKeyboard = null,
        #[Description('Requests clients to hide the keyboard as soon as it"s been used. The keyboard will still be available, but clients will automatically display the usual letter-keyboard in the chat - the user can press a special button in the input field to see the custom keyboard again. Defaults to _false_.')]
        public ?bool $oneTimeKeyboard = null,
        #[Description('The placeholder to be shown in the input field when the keyboard is active; 1-64 characters')]
        public ?string $inputFieldPlaceholder = null,
        #[Description('Use this parameter if you want to show the keyboard to specific users only. Targets: 1) users that are @mentioned in the _text_ of the [Message](https://core.telegram.org/bots/api#message) object; 2) if the bot"s message is a reply to a message in the same chat and forum topic, sender of the original message.; ; _Example:_ A user requests to change the bot"s language, bot replies to the request with a keyboard to select the new language. Other users in the group don"t see the keyboard.')]
        public ?bool $selective = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ReplyKeyboardMarkup;
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
{"keyboard":{"property":"keyboard","tgPropName":"keyboard","types":[["mixed"]],"tgTypes":[{"type":"array","of":{"type":"array","of":{"type":"union","types":[{"type":"str"},{"type":"api-type","name":"KeyboardButton"}]}}}],"nullable":false,"required":true},"is_persistent":{"property":"isPersistent","tgPropName":"is_persistent","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"resize_keyboard":{"property":"resizeKeyboard","tgPropName":"resize_keyboard","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"one_time_keyboard":{"property":"oneTimeKeyboard","tgPropName":"one_time_keyboard","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"input_field_placeholder":{"property":"inputFieldPlaceholder","tgPropName":"input_field_placeholder","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"selective":{"property":"selective","tgPropName":"selective","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
