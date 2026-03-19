<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes a service message about a unique gift that was sent or received.')]
#[See('https://core.telegram.org/bots/api#uniquegiftinfo')]
class UniqueGiftInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Information about the gift')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UniqueGiftTypeDTO $gift,
        #[Description('Origin of the gift. Currently, either “upgrade” for gifts upgraded from regular gifts, “transfer” for gifts transferred from other users or channels, “resale” for gifts bought from other users, “gifted\_upgrade” for upgrades purchased after the gift was sent, or “offer” for gifts bought or sold through gift purchase offers')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\OriginEnum $origin,
        #[Description('For gifts bought from other users, the currency in which the payment for the gift was done. Currently, one of “XTR” for Telegram Stars or “TON” for toncoins.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\Enum\LastResaleCurrencyEnum $lastResaleCurrency = null,
        #[Description('For gifts bought from other users, the price paid for the gift in either Telegram Stars or nanotoncoins')]
        public ?int $lastResaleAmount = null,
        #[Description('Unique identifier of the received gift for the bot; only present for gifts received on behalf of business accounts')]
        public ?string $ownedGiftId = null,
        #[Description('Number of Telegram Stars that must be paid to transfer the gift; omitted if the bot cannot transfer the gift')]
        public ?int $transferStarCount = null,
        #[Description('Point in time (Unix timestamp) when the gift can be transferred. If it is in the past, then the gift can be transferred now')]
        public ?int $nextTransferDate = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UniqueGiftInfo;
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
{"gift":{"property":"gift","tgPropName":"gift","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UniqueGiftTypeDTO"],"tgTypes":[{"type":"api-type","name":"UniqueGift"}],"nullable":false,"required":true},"origin":{"property":"origin","tgPropName":"origin","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\OriginEnum"],"tgTypes":[{"type":"str","literal":"upgrade"},{"type":"str","literal":"transfer"},{"type":"str","literal":"resale"},{"type":"str","literal":"gifted_upgrade"},{"type":"str","literal":"offer"}],"nullable":false,"required":true},"last_resale_currency":{"property":"lastResaleCurrency","tgPropName":"last_resale_currency","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\LastResaleCurrencyEnum"],"tgTypes":[{"type":"str","literal":"XTR"},{"type":"str","literal":"TON"}],"nullable":true,"required":false},"last_resale_amount":{"property":"lastResaleAmount","tgPropName":"last_resale_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"owned_gift_id":{"property":"ownedGiftId","tgPropName":"owned_gift_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"transfer_star_count":{"property":"transferStarCount","tgPropName":"transfer_star_count","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"next_transfer_date":{"property":"nextTransferDate","tgPropName":"next_transfer_date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
