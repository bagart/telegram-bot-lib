<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents a button to be shown above inline query results. You **must** use exactly one of the optional fields.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultsbutton')]
class InlineQueryResultsButtonTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Label text on the button')]
        public string $text,
        #[Description('Description of the [Web App](https://core.telegram.org/bots/webapps) that will be launched when the user presses the button. The Web App will be able to switch back to the inline mode using the method [switchInlineQuery](https://core.telegram.org/bots/webapps#initializing-mini-apps) inside the Web App.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\WebAppInfoTypeDTO $webApp = null,
        #[Description('[Deep-linking](https://core.telegram.org/bots/features#deep-linking) parameter for the /start message sent to the bot when a user presses the button. 1-64 characters, only `A-Z`, `a-z`, `0-9`, `_` and `-` are allowed.; ; _Example:_ An inline bot that sends YouTube videos can ask the user to connect the bot to their YouTube account to adapt search results accordingly. To do this, it displays a "Connect your YouTube account" button above the results, or even before showing any. The user presses the button, switches to a private chat with the bot and, in doing so, passes a start parameter that instructs the bot to return an OAuth link. Once done, the bot can offer a [_switch\_inline_](https://core.telegram.org/bots/api#inlinekeyboardmarkup) button so that the user can easily return to the chat where they wanted to use the bot"s inline capabilities.')]
        public ?string $startParameter = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultsButton;
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
{"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"web_app":{"property":"webApp","tgPropName":"web_app","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\WebAppInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"WebAppInfo"}],"nullable":true,"required":false},"start_parameter":{"property":"startParameter","tgPropName":"start_parameter","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
