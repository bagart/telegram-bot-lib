<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The section of the user"s Telegram Passport which has the issue, one of “passport”, “driver\_license”, “identity\_card”, “internal\_passport”')]
#[See('https://core.telegram.org/bots/api#passportelementerrorselfie')]
enum PassportElementErrorSelfiePropTypeEnum: string implements TgApiEnumContract
{
    case PASSPORT = 'passport';
    case DRIVER_LICENSE = 'driver_license';
    case IDENTITY_CARD = 'identity_card';
    case INTERNAL_PASSPORT = 'internal_passport';
}
