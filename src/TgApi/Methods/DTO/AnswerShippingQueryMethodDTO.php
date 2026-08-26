<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('If you sent an invoice requesting a shipping address and the parameter _is\_flexible_ was specified, the Bot API will send an [Update](https://core.telegram.org/bots/api#update) with a _shipping\_query_ field to the bot. Use this method to reply to shipping queries. On success, _True_ is returned.')]
#[See('https://core.telegram.org/bots/api#answershippingquery')]
class AnswerShippingQueryMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the query to be answered')]
        public string $shippingQueryId,
        #[Description('Pass _True_ if delivery to the specified address is possible and _False_ if there are any problems (for example, if delivery to the specified address is not possible)')]
        public bool $ok,
        #[Description('Required if _ok_ is _True_. An array of available shipping options.')]
        public ?array $shippingOptions = null,
        #[Description('Required if _ok_ is _False_. Error message in human readable form that explains why it is impossible to complete the order (e.g. “Sorry, delivery to your desired address is unavailable”). Telegram will display this message to the user.')]
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
        return TgApiMethodsEnum::answerShippingQuery;
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
{"shipping_query_id":{"property":"shippingQueryId","tgPropName":"shipping_query_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"ok":{"property":"ok","tgPropName":"ok","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"shipping_options":{"property":"shippingOptions","tgPropName":"shipping_options","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ShippingOptionTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"ShippingOption"}}],"nullable":true,"required":false},"error_message":{"property":"errorMessage","tgPropName":"error_message","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
