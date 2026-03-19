<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Contains information about the affiliate that received a commission via this transaction.')]
#[See('https://core.telegram.org/bots/api#affiliateinfo')]
class AffiliateInfoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('The number of Telegram Stars received by the affiliate for each 1000 Telegram Stars received by the bot from referred users')]
        public int $commissionPerMille,
        #[Description('Integer amount of Telegram Stars received by the affiliate from the transaction, rounded to 0; can be negative for refunds')]
        public int $amount,
        #[Description('The bot or the user that received an affiliate commission if it was received by a bot or a user')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $affiliateUser = null,
        #[Description('The chat that received an affiliate commission if it was received by a chat')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatTypeDTO $affiliateChat = null,
        #[Description('The number of 1/1000000000 shares of Telegram Stars received by the affiliate; from -999999999 to 999999999; can be negative for refunds')]
        public ?int $nanostarAmount = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::AffiliateInfo;
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
{"affiliate_user":{"property":"affiliateUser","tgPropName":"affiliate_user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":true,"required":false},"affiliate_chat":{"property":"affiliateChat","tgPropName":"affiliate_chat","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatTypeDTO"],"tgTypes":[{"type":"api-type","name":"Chat"}],"nullable":true,"required":false},"commission_per_mille":{"property":"commissionPerMille","tgPropName":"commission_per_mille","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"amount":{"property":"amount","tgPropName":"amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"nanostar_amount":{"property":"nanostarAmount","tgPropName":"nanostar_amount","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
