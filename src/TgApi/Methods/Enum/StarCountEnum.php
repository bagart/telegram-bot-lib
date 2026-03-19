<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Number of Telegram Stars to pay for the Telegram Premium subscription; must be 1000 for 3 months, 1500 for 6 months, and 2500 for 12 months')]
#[See('https://core.telegram.org/bots/api#giftpremiumsubscription')]
enum StarCountEnum: int implements TgApiEnumContract
{
    case STARS_3M = 1000;
    case STARS_6M = 1500;
    case STARS_12M = 2500;
}
