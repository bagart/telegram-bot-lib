<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents the [content](https://core.telegram.org/bots/api#inputmessagecontent) of an invoice message to be sent as the result of an inline query.')]
#[See('https://core.telegram.org/bots/api#inputinvoicemessagecontent')]
class InputInvoiceMessageContentTypeDTO implements TgApiTypeDTOContract
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
        #[Description('Payment provider token, obtained via [@BotFather](https://t.me/botfather). Pass an empty string for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?string $providerToken = null,
        #[Description('The maximum accepted amount for tips in the _smallest units_ of the currency (integer, **not** float/double). For example, for a maximum tip of `US$ 1.45` pass `max_tip_amount = 145`. See the _exp_ parameter in [currencies.json](https://core.telegram.org/bots/payments/currencies.json), it shows the number of digits past the decimal point for each currency (2 for the majority of currencies). Defaults to 0. Not supported for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?int $maxTipAmount = null,
        #[Description('An array of suggested amounts of tip in the _smallest units_ of the currency (integer, **not** float/double). At most 4 suggested tip amounts can be specified. The suggested tip amounts must be positive, passed in a strictly increased order and must not exceed _max\_tip\_amount_.')]
        public ?array $suggestedTipAmounts = null,
        #[Description('A JSON-serialized object for data about the invoice, which will be shared with the payment provider. A detailed description of the required fields should be provided by the payment provider.')]
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

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InputInvoiceMessageContent;
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
{"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"payload":{"property":"payload","tgPropName":"payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"provider_token":{"property":"providerToken","tgPropName":"provider_token","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"currency":{"property":"currency","tgPropName":"currency","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"prices":{"property":"prices","tgPropName":"prices","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LabeledPriceTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"LabeledPrice"}}],"nullable":false,"required":true},"max_tip_amount":{"property":"maxTipAmount","tgPropName":"max_tip_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"suggested_tip_amounts":{"property":"suggestedTipAmounts","tgPropName":"suggested_tip_amounts","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":true,"required":false},"provider_data":{"property":"providerData","tgPropName":"provider_data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo_url":{"property":"photoUrl","tgPropName":"photo_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo_size":{"property":"photoSize","tgPropName":"photo_size","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"photo_width":{"property":"photoWidth","tgPropName":"photo_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"photo_height":{"property":"photoHeight","tgPropName":"photo_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"need_name":{"property":"needName","tgPropName":"need_name","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_phone_number":{"property":"needPhoneNumber","tgPropName":"need_phone_number","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_email":{"property":"needEmail","tgPropName":"need_email","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_shipping_address":{"property":"needShippingAddress","tgPropName":"need_shipping_address","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"send_phone_number_to_provider":{"property":"sendPhoneNumberToProvider","tgPropName":"send_phone_number_to_provider","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"send_email_to_provider":{"property":"sendEmailToProvider","tgPropName":"send_email_to_provider","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"is_flexible":{"property":"isFlexible","tgPropName":"is_flexible","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
