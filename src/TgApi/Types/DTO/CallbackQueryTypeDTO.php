<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents an incoming callback query from a callback button in an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards). If the button that originated the query was attached to a message sent by the bot, the field _message_ will be present. If the button was attached to a message sent via the bot (in [inline mode](https://core.telegram.org/bots/api#inline-mode)), the field _inline\_message\_id_ will be present. Exactly one of the fields _data_ or _game\_short\_name_ will be present.; ; > **NOTE:** After the user presses a callback button, Telegram clients will display a progress bar until you call [answerCallbackQuery](https://core.telegram.org/bots/api#answercallbackquery). It is, therefore, necessary to react by calling [answerCallbackQuery](https://core.telegram.org/bots/api#answercallbackquery) even if no notification to the user is needed (e.g., without specifying any of the optional parameters).')]
#[See('https://core.telegram.org/bots/api#callbackquery')]
class CallbackQueryTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this query')]
        public string $id,
        #[Description('Sender')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $from,
        #[Description('Global identifier, uniquely corresponding to the chat to which the message with the callback button was sent. Useful for high scores in [games](https://core.telegram.org/bots/api#games).')]
        public string $chatInstance,
        #[Description('Message sent by the bot with the callback button that originated the query')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\MaybeInaccessibleMessageTypeDTO $message = null,
        #[Description('Identifier of the message sent via the bot in inline mode, that originated the query.')]
        public ?string $inlineMessageId = null,
        #[Description('Data associated with the callback button. Be aware that the message originated the query can contain no callback buttons with this data.')]
        public ?string $data = null,
        #[Description('Short name of a [Game](https://core.telegram.org/bots/api#games) to be returned, serves as the unique identifier for the game')]
        public ?string $gameShortName = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::CallbackQuery;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"from":{"property":"from","tgPropName":"from","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"message":{"property":"message","tgPropName":"message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MaybeInaccessibleMessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"MaybeInaccessibleMessage"}],"nullable":true,"required":false},"inline_message_id":{"property":"inlineMessageId","tgPropName":"inline_message_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat_instance":{"property":"chatInstance","tgPropName":"chat_instance","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"data":{"property":"data","tgPropName":"data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"game_short_name":{"property":"gameShortName","tgPropName":"game_short_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
