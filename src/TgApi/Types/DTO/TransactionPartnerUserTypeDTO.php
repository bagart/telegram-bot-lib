<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a transaction with a user.')]
#[See('https://core.telegram.org/bots/api#transactionpartneruser')]
class TransactionPartnerUserTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Type of the transaction, currently one of “invoice\_payment” for payments via invoices, “paid\_media\_payment” for payments for paid media, “gift\_purchase” for gifts sent by the bot, “premium\_purchase” for Telegram Premium subscriptions gifted by the bot, “business\_account\_transfer” for direct transfers from managed business accounts')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\TransactionTypeEnum $transactionType,
        #[Description('Information about the user')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user,
        #[Description('Type of the transaction partner, always “user”')]
        public string $type = 'user',
        #[Description('Information about the affiliate that received a commission via this transaction. Can be available only for “invoice\_payment” and “paid\_media\_payment” transactions.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\AffiliateInfoTypeDTO $affiliate = null,
        #[Description('Bot-specified invoice payload. Can be available only for “invoice\_payment” transactions.')]
        public ?string $invoicePayload = null,
        #[Description('The duration of the paid subscription. Can be available only for “invoice\_payment” transactions.')]
        public ?int $subscriptionPeriod = null,
        #[Description('Information about the paid media bought by the user; for “paid\_media\_payment” transactions only')]
        public ?array $paidMedia = null,
        #[Description('Bot-specified paid media payload. Can be available only for “paid\_media\_payment” transactions.')]
        public ?string $paidMediaPayload = null,
        #[Description('The gift sent to the user by the bot; for “gift\_purchase” transactions only')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\GiftTypeDTO $gift = null,
        #[Description('Number of months the gifted Telegram Premium subscription will be active for; for “premium\_purchase” transactions only')]
        public ?int $premiumSubscriptionDuration = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::TransactionPartnerUser;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"user"}],"nullable":false,"required":true},"transaction_type":{"property":"transactionType","tgPropName":"transaction_type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\TransactionTypeEnum"],"tgTypes":[{"type":"str","literal":"invoice_payment"},{"type":"str","literal":"paid_media_payment"},{"type":"str","literal":"gift_purchase"},{"type":"str","literal":"premium_purchase"},{"type":"str","literal":"business_account_transfer"}],"nullable":false,"required":true},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"affiliate":{"property":"affiliate","tgPropName":"affiliate","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\AffiliateInfoTypeDTO"],"tgTypes":[{"type":"api-type","name":"AffiliateInfo"}],"nullable":true,"required":false},"invoice_payload":{"property":"invoicePayload","tgPropName":"invoice_payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"subscription_period":{"property":"subscriptionPeriod","tgPropName":"subscription_period","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"paid_media":{"property":"paidMedia","tgPropName":"paid_media","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PaidMediaTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PaidMedia"}}],"nullable":true,"required":false},"paid_media_payload":{"property":"paidMediaPayload","tgPropName":"paid_media_payload","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"gift":{"property":"gift","tgPropName":"gift","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\GiftTypeDTO"],"tgTypes":[{"type":"api-type","name":"Gift"}],"nullable":true,"required":false},"premium_subscription_duration":{"property":"premiumSubscriptionDuration","tgPropName":"premium_subscription_duration","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
