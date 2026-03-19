<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is contract but not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the source of a transaction, or its recipient for outgoing transactions. Currently, it can be one of; ; -   [TransactionPartnerUser](https://core.telegram.org/bots/api#transactionpartneruser); -   [TransactionPartnerChat](https://core.telegram.org/bots/api#transactionpartnerchat); -   [TransactionPartnerAffiliateProgram](https://core.telegram.org/bots/api#transactionpartneraffiliateprogram); -   [TransactionPartnerFragment](https://core.telegram.org/bots/api#transactionpartnerfragment); -   [TransactionPartnerTelegramAds](https://core.telegram.org/bots/api#transactionpartnertelegramads); -   [TransactionPartnerTelegramApi](https://core.telegram.org/bots/api#transactionpartnertelegramapi); -   [TransactionPartnerOther](https://core.telegram.org/bots/api#transactionpartnerother)')]
#[See('https://core.telegram.org/bots/api#transactionpartner')]
class TransactionPartnerTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiTypesEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::TransactionPartner;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
