<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains basic information about a refunded payment.')]
#[See('https://core.telegram.org/bots/api#refundedpayment')]
class RefundedPaymentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Total refunded price in the _smallest units_ of the currency (integer, **not** float/double). For example, for a price of `US$ 1.45`, `total_amount = 145`. See the _exp_ parameter in [currencies.json](https://core.telegram.org/bots/payments/currencies.json), it shows the number of digits past the decimal point for each currency (2 for the majority of currencies).')]
        public int $totalAmount,
        #[Description('Bot-specified invoice payload')]
        public string $invoicePayload,
        #[Description('Telegram payment identifier')]
        public string $telegramPaymentChargeId,
        #[Description('Three-letter ISO 4217 [currency](https://core.telegram.org/bots/payments#supported-currencies) code, or “XTR” for payments in [Telegram Stars](https://t.me/BotNews/90). Currently, always “XTR”')]
        public string $currency = 'XTR',
        #[Description('Provider payment identifier')]
        public ?string $providerPaymentChargeId = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::RefundedPayment;
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
{"currency":{"property":"currency","tgPropName":"currency","types":["string"],"tgTypes":[{"type":"str","literal":"XTR"}],"nullable":false,"required":true},"total_amount":{"property":"totalAmount","tgPropName":"total_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"invoice_payload":{"property":"invoicePayload","tgPropName":"invoice_payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"telegram_payment_charge_id":{"property":"telegramPaymentChargeId","tgPropName":"telegram_payment_charge_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"provider_payment_charge_id":{"property":"providerPaymentChargeId","tgPropName":"provider_payment_charge_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
