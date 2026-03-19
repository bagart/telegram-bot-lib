<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Upon receiving a message with this object, Telegram clients will remove the current custom keyboard and display the default letter-keyboard. By default, custom keyboards are displayed until a new keyboard is sent by a bot. An exception is made for one-time keyboards that are hidden immediately after the user presses a button (see [ReplyKeyboardMarkup](https://core.telegram.org/bots/api#replykeyboardmarkup)). Not supported in channels and for messages sent on behalf of a Telegram Business account.')]
#[See('https://core.telegram.org/bots/api#replykeyboardremove')]
class ReplyKeyboardRemoveTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Requests clients to remove the custom keyboard (user will not be able to summon this keyboard; if you want to hide the keyboard from sight but keep it accessible, use _one\_time\_keyboard_ in [ReplyKeyboardMarkup](https://core.telegram.org/bots/api#replykeyboardmarkup))')]
        public bool $removeKeyboard = true,
        #[Description('Use this parameter if you want to remove the keyboard for specific users only. Targets: 1) users that are @mentioned in the _text_ of the [Message](https://core.telegram.org/bots/api#message) object; 2) if the bot"s message is a reply to a message in the same chat and forum topic, sender of the original message.; ; _Example:_ A user votes in a poll, bot returns confirmation message in reply to the vote and removes the keyboard for that user, while still showing the keyboard with poll options to users who haven"t voted yet.')]
        public ?bool $selective = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ReplyKeyboardRemove;
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
{"remove_keyboard":{"property":"removeKeyboard","tgPropName":"remove_keyboard","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":false,"required":true},"selective":{"property":"selective","tgPropName":"selective","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
