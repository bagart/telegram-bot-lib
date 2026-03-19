<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains basic information about a successful payment. Note that if the buyer initiates a chargeback with the relevant payment provider following this transaction, the funds may be debited from your balance. This is outside of Telegram"s control.')]
#[See('https://core.telegram.org/bots/api#successfulpayment')]
class SuccessfulPaymentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Three-letter ISO 4217 [currency](https://core.telegram.org/bots/payments#supported-currencies) code, or “XTR” for payments in [Telegram Stars](https://t.me/BotNews/90)')]
        public string $currency,
        #[Description('Total price in the _smallest units_ of the currency (integer, **not** float/double). For example, for a price of `US$ 1.45` pass `amount = 145`. See the _exp_ parameter in [currencies.json](https://core.telegram.org/bots/payments/currencies.json), it shows the number of digits past the decimal point for each currency (2 for the majority of currencies).')]
        public int $totalAmount,
        #[Description('Bot-specified invoice payload')]
        public string $invoicePayload,
        #[Description('Telegram payment identifier')]
        public string $telegramPaymentChargeId,
        #[Description('Provider payment identifier')]
        public string $providerPaymentChargeId,
        #[Description('Expiration date of the subscription, in Unix time; for recurring payments only')]
        public ?int $subscriptionExpirationDate = null,
        #[Description('_True_, if the payment is a recurring payment for a subscription')]
        public ?bool $isRecurring = true,
        #[Description('_True_, if the payment is the first payment for a subscription')]
        public ?bool $isFirstRecurring = true,
        #[Description('Identifier of the shipping option chosen by the user')]
        public ?string $shippingOptionId = null,
        #[Description('Order information provided by the user')]
        public ?OrderInfoTypeDTO $orderInfo = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::SuccessfulPayment;
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
{"currency":{"property":"currency","tgPropName":"currency","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"total_amount":{"property":"totalAmount","tgPropName":"total_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"invoice_payload":{"property":"invoicePayload","tgPropName":"invoice_payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"subscription_expiration_date":{"property":"subscriptionExpirationDate","tgPropName":"subscription_expiration_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"is_recurring":{"property":"isRecurring","tgPropName":"is_recurring","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"is_first_recurring":{"property":"isFirstRecurring","tgPropName":"is_first_recurring","types":["bool"],"tgTypes":[{"type":"bool","literal":true}],"nullable":true,"required":false},"shipping_option_id":{"property":"shippingOptionId","tgPropName":"shipping_option_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"order_info":{"property":"orderInfo","tgPropName":"order_info","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\OrderInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"OrderInfo"}],"nullable":true,"required":false},"telegram_payment_charge_id":{"property":"telegramPaymentChargeId","tgPropName":"telegram_payment_charge_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"provider_payment_charge_id":{"property":"providerPaymentChargeId","tgPropName":"provider_payment_charge_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true}}
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
