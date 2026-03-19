<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Number of months the Telegram Premium subscription will be active for the user; must be one of 3, 6, or 12')]
#[See('https://core.telegram.org/bots/api#giftpremiumsubscription')]
enum MonthCountEnum: int implements TgApiEnumContract
{
    case X3 = 3;
    case X6 = 6;
    case X12 = 12;
}
