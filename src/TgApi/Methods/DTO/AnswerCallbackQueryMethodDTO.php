<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to send answers to callback queries sent from [inline keyboards](https://core.telegram.org/bots/features#inline-keyboards). The answer will be displayed to the user as a notification at the top of the chat screen or as an alert. On success, _True_ is returned.; ; > Alternatively, the user can be redirected to the specified Game URL. For this option to work, you must first create a game for your bot via [@BotFather](https://t.me/botfather) and accept the terms. Otherwise, you may use links like `t.me/your_bot?start=XXXX` that open your bot with a parameter.')]
#[See('https://core.telegram.org/bots/api#answercallbackquery')]
class AnswerCallbackQueryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the query to be answered')]
        public string $callbackQueryId,
        #[Description('Text of the notification. If not specified, nothing will be shown to the user, 0-200 characters')]
        public ?string $text = null,
        #[Description('If _True_, an alert will be shown by the client instead of a notification at the top of the chat screen. Defaults to _false_.')]
        public ?bool $showAlert = null,
        #[Description('URL that will be opened by the user"s client. If you have created a [Game](https://core.telegram.org/bots/api#game) and accepted the conditions via [@BotFather](https://t.me/botfather), specify the URL that opens your game - note that this will only work if the query comes from a [_callback\_game_](https://core.telegram.org/bots/api#inlinekeyboardbutton) button.; ; Otherwise, you may use links like `t.me/your_bot?start=XXXX` that open your bot with a parameter.')]
        public ?string $url = null,
        #[Description('The maximum amount of time in seconds that the result of the callback query may be cached client-side. Telegram apps will support caching starting in version 3.14. Defaults to 0.')]
        public ?int $cacheTime = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::answerCallbackQuery;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"callback_query_id":{"property":"callbackQueryId","tgPropName":"callback_query_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"show_alert":{"property":"showAlert","tgPropName":"show_alert","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"cache_time":{"property":"cacheTime","tgPropName":"cache_time","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
