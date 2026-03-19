<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\ReplyParametersTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\SuggestedPostParametersTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to send invoices. On success, the sent [Message](https://core.telegram.org/bots/api#message) is returned.')]
#[See('https://core.telegram.org/bots/api#sendinvoice')]
class SendInvoiceMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
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
        #[Description('Unique identifier for the target message thread (topic) of a forum; for forum supergroups and private chats of bots with forum topic mode enabled only')]
        public ?int $messageThreadId = null,
        #[Description('Identifier of the direct messages topic to which the message will be sent; required if the message is sent to a direct messages chat')]
        public ?int $directMessagesTopicId = null,
        #[Description('Payment provider token, obtained via [@BotFather](https://t.me/botfather). Pass an empty string for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?string $providerToken = null,
        #[Description('The maximum accepted amount for tips in the _smallest units_ of the currency (integer, **not** float/double). For example, for a maximum tip of `US$ 1.45` pass `max_tip_amount = 145`. See the _exp_ parameter in [currencies.json](https://core.telegram.org/bots/payments/currencies.json), it shows the number of digits past the decimal point for each currency (2 for the majority of currencies). Defaults to 0. Not supported for payments in [Telegram Stars](https://t.me/BotNews/90).')]
        public ?int $maxTipAmount = null,
        #[Description('An array of suggested amounts of tips in the _smallest units_ of the currency (integer, **not** float/double). At most 4 suggested tip amounts can be specified. The suggested tip amounts must be positive, passed in a strictly increased order and must not exceed _max\_tip\_amount_.')]
        public ?array $suggestedTipAmounts = null,
        #[Description('Unique deep-linking parameter. If left empty, **forwarded copies** of the sent message will have a _Pay_ button, allowing multiple users to pay directly from the forwarded message, using the same invoice. If non-empty, forwarded copies of the sent message will have a _URL_ button with a deep link to the bot (instead of a _Pay_ button), with the value used as the start parameter')]
        public ?string $startParameter = null,
        #[Description('JSON-serialized data about the invoice, which will be shared with the payment provider. A detailed description of required fields should be provided by the payment provider.')]
        public ?string $providerData = null,
        #[Description('URL of the product photo for the invoice. Can be a photo of the goods or a marketing image for a service. People like it better when they see what they are paying for.')]
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
        #[Description('Sends the message [silently](https://telegram.org/blog/channels-2-0#silent-messages). Users will receive a notification with no sound.')]
        public ?bool $disableNotification = null,
        #[Description('Protects the contents of the sent message from forwarding and saving')]
        public ?bool $protectContent = null,
        #[Description('Pass _True_ to allow up to 1000 messages per second, ignoring [broadcasting limits](https://core.telegram.org/bots/faq#how-can-i-message-all-of-my-bot-39s-subscribers-at-once) for a fee of 0.1 Telegram Stars per message. The relevant Stars will be withdrawn from the bot"s balance')]
        public ?bool $allowPaidBroadcast = null,
        #[Description('Unique identifier of the message effect to be added to the message; for private chats only')]
        public ?string $messageEffectId = null,
        #[Description('An object containing the parameters of the suggested post to send; for direct messages chats only. If the message is sent as a reply to another suggested post, then that suggested post is automatically declined.')]
        public ?SuggestedPostParametersTypeDTO $suggestedPostParameters = null,
        #[Description('Description of the message to reply to')]
        public ?ReplyParametersTypeDTO $replyParameters = null,
        #[Description('An object for an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards). If empty, one "Pay `total price`" button will be shown. If not empty, the first button must be a Pay button.')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            MessageTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::sendInvoice;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"direct_messages_topic_id":{"property":"directMessagesTopicId","tgPropName":"direct_messages_topic_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"payload":{"property":"payload","tgPropName":"payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"provider_token":{"property":"providerToken","tgPropName":"provider_token","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"currency":{"property":"currency","tgPropName":"currency","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"prices":{"property":"prices","tgPropName":"prices","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LabeledPriceTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"LabeledPrice"}}],"nullable":false,"required":true},"max_tip_amount":{"property":"maxTipAmount","tgPropName":"max_tip_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"suggested_tip_amounts":{"property":"suggestedTipAmounts","tgPropName":"suggested_tip_amounts","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":true,"required":false},"start_parameter":{"property":"startParameter","tgPropName":"start_parameter","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"provider_data":{"property":"providerData","tgPropName":"provider_data","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo_url":{"property":"photoUrl","tgPropName":"photo_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo_size":{"property":"photoSize","tgPropName":"photo_size","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"photo_width":{"property":"photoWidth","tgPropName":"photo_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"photo_height":{"property":"photoHeight","tgPropName":"photo_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"need_name":{"property":"needName","tgPropName":"need_name","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_phone_number":{"property":"needPhoneNumber","tgPropName":"need_phone_number","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_email":{"property":"needEmail","tgPropName":"need_email","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"need_shipping_address":{"property":"needShippingAddress","tgPropName":"need_shipping_address","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"send_phone_number_to_provider":{"property":"sendPhoneNumberToProvider","tgPropName":"send_phone_number_to_provider","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"send_email_to_provider":{"property":"sendEmailToProvider","tgPropName":"send_email_to_provider","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"is_flexible":{"property":"isFlexible","tgPropName":"is_flexible","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"disable_notification":{"property":"disableNotification","tgPropName":"disable_notification","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"protect_content":{"property":"protectContent","tgPropName":"protect_content","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_paid_broadcast":{"property":"allowPaidBroadcast","tgPropName":"allow_paid_broadcast","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"message_effect_id":{"property":"messageEffectId","tgPropName":"message_effect_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"suggested_post_parameters":{"property":"suggestedPostParameters","tgPropName":"suggested_post_parameters","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SuggestedPostParametersTypeDTO"],"tgTypes":[{"type":"api-type","name":"SuggestedPostParameters"}],"nullable":true,"required":false},"reply_parameters":{"property":"replyParameters","tgPropName":"reply_parameters","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ReplyParametersTypeDTO"],"tgTypes":[{"type":"api-type","name":"ReplyParameters"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false}}
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
