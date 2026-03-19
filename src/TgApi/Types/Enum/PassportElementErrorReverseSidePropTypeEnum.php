<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The section of the user"s Telegram Passport which has the issue, one of “driver\_license”, “identity\_card”')]
#[See('https://core.telegram.org/bots/api#passportelementerrorreverseside')]
enum PassportElementErrorReverseSidePropTypeEnum: string implements TgApiEnumContract
{
    case DRIVER_LICENSE = 'driver_license';
    case IDENTITY_CARD = 'identity_card';
}
