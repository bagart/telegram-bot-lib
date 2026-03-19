<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Color of the topic icon in RGB format. Currently, must be one of 7322096 (0x6FB9F0), 16766590 (0xFFD67E), 13338331 (0xCB86DB), 9367192 (0x8EEE98), 16749490 (0xFF93B2), or 16478047 (0xFB6F5F)')]
#[See('https://core.telegram.org/bots/api#createforumtopic')]
enum IconColorEnum: int implements TgApiEnumContract
{
    case X7322096 = 7322096;
    case X16766590 = 16766590;
    case X13338331 = 13338331;
    case X9367192 = 9367192;
    case X16749490 = 16749490;
    case X16478047 = 16478047;
}
