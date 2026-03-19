<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object describes the state of a revenue withdrawal operation. Currently, it can be one of; ; -   [RevenueWithdrawalStatePending](https://core.telegram.org/bots/api#revenuewithdrawalstatepending); -   [RevenueWithdrawalStateSucceeded](https://core.telegram.org/bots/api#revenuewithdrawalstatesucceeded); -   [RevenueWithdrawalStateFailed](https://core.telegram.org/bots/api#revenuewithdrawalstatefailed)')]
#[See('https://core.telegram.org/bots/api#revenuewithdrawalstate')]
class RevenueWithdrawalStateTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::RevenueWithdrawalState;
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
