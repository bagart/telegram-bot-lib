<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about an incoming pre-checkout query.')]
#[See('https://core.telegram.org/bots/api#precheckoutquery')]
class PreCheckoutQueryTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique query identifier')]
        public string $id,
        #[Description('User who sent the query')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $from,
        #[Description('Three-letter ISO 4217 [currency](https://core.telegram.org/bots/payments#supported-currencies) code, or “XTR” for payments in [Telegram Stars](https://t.me/BotNews/90)')]
        public string $currency,
        #[Description('Total price in the _smallest units_ of the currency (integer, **not** float/double). For example, for a price of `US$ 1.45` pass `amount = 145`. See the _exp_ parameter in [currencies.json](https://core.telegram.org/bots/payments/currencies.json), it shows the number of digits past the decimal point for each currency (2 for the majority of currencies).')]
        public int $totalAmount,
        #[Description('Bot-specified invoice payload')]
        public string $invoicePayload,
        #[Description('Identifier of the shipping option chosen by the user')]
        public ?string $shippingOptionId = null,
        #[Description('Order information provided by the user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\OrderInfoTypeDTO $orderInfo = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PreCheckoutQuery;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"from":{"property":"from","tgPropName":"from","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"currency":{"property":"currency","tgPropName":"currency","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"total_amount":{"property":"totalAmount","tgPropName":"total_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"invoice_payload":{"property":"invoicePayload","tgPropName":"invoice_payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"shipping_option_id":{"property":"shippingOptionId","tgPropName":"shipping_option_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"order_info":{"property":"orderInfo","tgPropName":"order_info","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\OrderInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"OrderInfo"}],"nullable":true,"required":false}}
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
