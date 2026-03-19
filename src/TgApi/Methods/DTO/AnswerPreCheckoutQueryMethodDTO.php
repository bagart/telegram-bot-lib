<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Once the user has confirmed their payment and shipping details, the Bot API sends the final confirmation in the form of an [Update](https://core.telegram.org/bots/api#update) with the field _pre\_checkout\_query_. Use this method to respond to such pre-checkout queries. On success, _True_ is returned. **Note:** The Bot API must receive an answer within 10 seconds after the pre-checkout query was sent.')]
#[See('https://core.telegram.org/bots/api#answerprecheckoutquery')]
class AnswerPreCheckoutQueryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the query to be answered')]
        public string $preCheckoutQueryId,
        #[Description('Specify _True_ if everything is alright (goods are available, etc.) and the bot is ready to proceed with the order. Use _False_ if there are any problems.')]
        public bool $ok,
        #[Description('Required if _ok_ is _False_. Error message in human readable form that explains the reason for failure to proceed with the checkout (e.g. "Sorry, somebody just bought the last of our amazing black T-shirts while you were busy filling out your payment details. Please choose a different color or garment!"). Telegram will display this message to the user.')]
        public ?string $errorMessage = null,
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
        return TgApiMethodsEnum::answerPreCheckoutQuery;
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
{"pre_checkout_query_id":{"property":"preCheckoutQueryId","tgPropName":"pre_checkout_query_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"ok":{"property":"ok","tgPropName":"ok","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"error_message":{"property":"errorMessage","tgPropName":"error_message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
