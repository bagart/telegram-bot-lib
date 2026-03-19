<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a Telegram Star transaction. Note that if the buyer initiates a chargeback with the payment provider from whom they acquired Stars (e.g., Apple, Google) following this transaction, the refunded Stars will be deducted from the bot"s balance. This is outside of Telegram"s control.')]
#[See('https://core.telegram.org/bots/api#startransaction')]
class StarTransactionTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the transaction. Coincides with the identifier of the original transaction for refund transactions. Coincides with _SuccessfulPayment.telegram\_payment\_charge\_id_ for successful incoming payments from users.')]
        public string $id,
        #[Description('Integer amount of Telegram Stars transferred by the transaction')]
        public int $amount,
        #[Description('Date the transaction was created in Unix time')]
        public int $date,
        #[Description('The number of 1/1000000000 shares of Telegram Stars transferred by the transaction; from 0 to 999999999')]
        public ?int $nanostarAmount = null,
        #[Description('Source of an incoming transaction (e.g., a user purchasing goods or services, Fragment refunding a failed withdrawal). Only for incoming transactions')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerTypeDTO $source = null,
        #[Description('Receiver of an outgoing transaction (e.g., a user for a purchase refund, Fragment for a withdrawal). Only for outgoing transactions')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\TransactionPartnerTypeDTO $receiver = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::StarTransaction;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"amount":{"property":"amount","tgPropName":"amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"nanostar_amount":{"property":"nanostarAmount","tgPropName":"nanostar_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"source":{"property":"source","tgPropName":"source","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\TransactionPartnerTypeDTO"],"tgTypes":[{"type":"api-type","name":"TransactionPartner"}],"nullable":true,"required":false},"receiver":{"property":"receiver","tgPropName":"receiver","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\TransactionPartnerTypeDTO"],"tgTypes":[{"type":"api-type","name":"TransactionPartner"}],"nullable":true,"required":false}}
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
