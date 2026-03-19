<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('For gifts bought from other users, the currency in which the payment for the gift was done. Currently, one of “XTR” for Telegram Stars or “TON” for toncoins.')]
#[See('https://core.telegram.org/bots/api#uniquegiftinfo')]
enum LastResaleCurrencyEnum: string implements TgApiEnumContract
{
    case XTR = 'XTR';
    case TON = 'TON';
}
