<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\StarAmountTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('A method to get the current Telegram Stars balance of the bot. Requires no parameters. On success, returns a [StarAmount](https://core.telegram.org/bots/api#staramount) object.')]
#[See('https://core.telegram.org/bots/api#getmystarbalance')]
class GetMyStarBalanceMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiMethodsEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::getMyStarBalance;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    public static function getReturnTypes(): array
    {
        return [
            StarAmountTypeDTO::class,
        ];
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
