<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of element of the user"s Telegram Passport which has the issue, one of “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration”, “temporary\_registration”')]
#[See('https://core.telegram.org/bots/api#passportelementerrortranslationfile')]
enum PassportElementErrorTranslationFilePropTypeEnum: string implements TgApiEnumContract
{
    case PASSPORT = 'passport';
    case DRIVER_LICENSE = 'driver_license';
    case IDENTITY_CARD = 'identity_card';
    case INTERNAL_PASSPORT = 'internal_passport';
    case UTILITY_BILL = 'utility_bill';
    case BANK_STATEMENT = 'bank_statement';
    case RENTAL_AGREEMENT = 'rental_agreement';
    case PASSPORT_REGISTRATION = 'passport_registration';
    case TEMPORARY_REGISTRATION = 'temporary_registration';
}
