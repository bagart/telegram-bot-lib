<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to create a link for an invoice. Returns the created invoice link as _String_ on success.')]
#[See('https://core.telegram.org/bots/api#createinvoicelink')]
class CreateInvoiceLinkMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Product name, 1-32 characters')]
        public string $title,
        #[Description('Product description, 1-255 characters')]
        public string $description,
        #[Description('Bot-defined invoice payload, 1-128 bytes. This will not be displayed to the user, use it for your internal processes.')]
        public string $payload,
        #[Description('Three-letter ISO 4217 currency code, see [more on currencies](https://core.telegram.org/bots/payments#supported-currencies). Pass “XTR” for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public string $currency,
        #[Description('Price breakdown, an array of components (e.g. product price, tax, discount, delivery cost, delivery tax, bonus, etc.). Must contain exactly one item for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public array $prices,
        #[Description('Unique identifier of the business connection on behalf of which the link will be created. For payments in [Telegram Stars](https://t.me/BotNews/90) only.')]
        public ?string $businessConnectionId = null,
        #[Description('Payment provider token, obtained via [@BotFather](https://t.me/botfather). Pass an empty string for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?string $providerToken = null,
        #[Description('The number of seconds the subscription will be active for before the next payment. The currency must be set to “XTR” (Telegram Stars) if the parameter is used. Currently, it must always be 2592000 (30 days) if specified. Any number of subscriptions can be active for a given bot at the same time, including multiple concurrent subscriptions from the same user. Subscription price must no exceed 10000 Telegram Stars.')]
        public ?int $subscriptionPeriod = 2592000,
        #[Description('The maximum accepted amount for tips in the _smallest units_ of the currency (integer, **not** float/double). For example, for a maximum tip of `US$ 1.45` pass `max_tip_amount = 145`. See the _exp_ parameter in [currencies.json](https://core.telegram.org/bots/payments/currencies.json), it shows the number of digits past the decimal point for each currency (2 for the majority of currencies). Defaults to 0. Not supported for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?int $maxTipAmount = null,
        #[Description('An array of suggested amounts of tips in the _smallest units_ of the currency (integer, **not** float/double). At most 4 suggested tip amounts can be specified. The suggested tip amounts must be positive, passed in a strictly increased order and must not exceed _max\_tip\_amount_.')]
        public ?array $suggestedTipAmounts = null,
        #[Description('JSON-serialized data about the invoice, which will be shared with the payment provider. A detailed description of required fields should be provided by the payment provider.')]
        public ?string $providerData = null,
        #[Description('URL of the product photo for the invoice. Can be a photo of the goods or a marketing image for a service.')]
        public ?string $photoUrl = null,
        #[Description('Photo size in bytes')]
        public ?int $photoSize = null,
        #[Description('Photo width')]
        public ?int $photoWidth = null,
        #[Description('Photo height')]
        public ?int $photoHeight = null,
        #[Description('Pass _True_ if you require the user"s full name to complete the order. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $needName = null,
        #[Description('Pass _True_ if you require the user"s phone number to complete the order. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $needPhoneNumber = null,
        #[Description('Pass _True_ if you require the user"s email address to complete the order. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $needEmail = null,
        #[Description('Pass _True_ if you require the user"s shipping address to complete the order. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $needShippingAddress = null,
        #[Description('Pass _True_ if the user"s phone number should be sent to the provider. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $sendPhoneNumberToProvider = null,
        #[Description('Pass _True_ if the user"s email address should be sent to the provider. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $sendEmailToProvider = null,
        #[Description('Pass _True_ if the final price depends on the shipping method. Ignored for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?bool $isFlexible = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'string',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::createInvoiceLink;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"payload":{"property":"payload","tgPropName":"payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"provider_token":{"property":"providerToken","tgPropName":"provider_token","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"currency":{"property":"currency","tgPropName":"currency","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"prices":{"property":"prices","tgPropName":"prices","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LabeledPriceTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"LabeledPrice"}}],"nullable":false,"required":true},"subscription_period":{"property":"subscriptionPeriod","tgPropName":"subscription_period","types":["int"],"tgTypes":[{"type":"int32","literal":2592000}],"nullable":true,"required":false},"max_tip_amount":{"property":"maxTipAmount","tgPropName":"max_tip_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"suggested_tip_amounts":{"property":"suggestedTipAmounts","tgPropName":"suggested_tip_amounts","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":true,"required":false},"provider_data":{"property":"providerData","tgPropName":"provider_data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo_url":{"property":"photoUrl","tgPropName":"photo_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo_size":{"property":"photoSize","tgPropName":"photo_size","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"photo_width":{"property":"photoWidth","tgPropName":"photo_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"photo_height":{"property":"photoHeight","tgPropName":"photo_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"need_name":{"property":"needName","tgPropName":"need_name","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_phone_number":{"property":"needPhoneNumber","tgPropName":"need_phone_number","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_email":{"property":"needEmail","tgPropName":"need_email","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_shipping_address":{"property":"needShippingAddress","tgPropName":"need_shipping_address","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"send_phone_number_to_provider":{"property":"sendPhoneNumberToProvider","tgPropName":"send_phone_number_to_provider","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"send_email_to_provider":{"property":"sendEmailToProvider","tgPropName":"send_email_to_provider","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"is_flexible":{"property":"isFlexible","tgPropName":"is_flexible","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
