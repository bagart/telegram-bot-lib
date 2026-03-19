<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The section of the user"s Telegram Passport which has the error, one of “personal\_details”, “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “address”')]
#[See('https://core.telegram.org/bots/api#passportelementerrordatafield')]
enum PassportElementErrorDataFieldPropTypeEnum: string implements TgApiEnumContract
{
    case PERSONAL_DETAILS = 'personal_details';
    case PASSPORT = 'passport';
    case DRIVER_LICENSE = 'driver_license';
    case IDENTITY_CARD = 'identity_card';
    case INTERNAL_PASSPORT = 'internal_passport';
    case ADDRESS = 'address';
}
