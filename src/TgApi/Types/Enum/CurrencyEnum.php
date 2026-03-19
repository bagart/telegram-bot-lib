<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Currency in which the post will be paid. Currently, must be one of “XTR” for Telegram Stars or “TON” for toncoins')]
#[See('https://core.telegram.org/bots/api#suggestedpostprice')]
enum CurrencyEnum: string implements TgApiEnumContract
{
    case XTR = 'XTR';
    case TON = 'TON';
}
