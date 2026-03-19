<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\CurrencyEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a service message about a successful payment for a suggested post.')]
#[See('https://core.telegram.org/bots/api#suggestedpostpaid')]
class SuggestedPostPaidTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Currency in which the payment was made. Currently, one of “XTR” for Telegram Stars or “TON” for toncoins')]
        public CurrencyEnum $currency,
        #[Description('Message containing the suggested post. Note that the [Message](https://core.telegram.org/bots/api#message) object in this field will not contain the _reply\_to\_message_ field even if it itself is a reply.')]
        public ?MessageTypeDTO $suggestedPostMessage = null,
        #[Description('The amount of the currency that was received by the channel in nanotoncoins; for payments in toncoins only')]
        public ?int $amount = null,
        #[Description('The amount of Telegram Stars that was received by the channel; for payments in Telegram Stars only')]
        public ?StarAmountTypeDTO $starAmount = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::SuggestedPostPaid;
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
{"suggested_post_message":{"property":"suggestedPostMessage","tgPropName":"suggested_post_message","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageTypeDTO"],"tgTypes":[{"type":"api-type","name":"Message"}],"nullable":true,"required":false},"currency":{"property":"currency","tgPropName":"currency","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\CurrencyEnum"],"tgTypes":[{"type":"str","literal":"XTR"},{"type":"str","literal":"TON"}],"nullable":false,"required":true},"amount":{"property":"amount","tgPropName":"amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"star_amount":{"property":"starAmount","tgPropName":"star_amount","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\StarAmountTypeDTO"],"tgTypes":[{"type":"api-type","name":"StarAmount"}],"nullable":true,"required":false}}
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
