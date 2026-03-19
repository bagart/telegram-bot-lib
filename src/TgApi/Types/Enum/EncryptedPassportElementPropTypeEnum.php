<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Element type. One of “personal\_details”, “passport”, “driver\_license”, “identity\_card”, “internal\_passport”, “address”, “utility\_bill”, “bank\_statement”, “rental\_agreement”, “passport\_registration”, “temporary\_registration”, “phone\_number”, “email”.')]
#[See('https://core.telegram.org/bots/api#encryptedpassportelement')]
enum EncryptedPassportElementPropTypeEnum: string implements TgApiEnumContract
{
    case PERSONAL_DETAILS = 'personal_details';
    case PASSPORT = 'passport';
    case DRIVER_LICENSE = 'driver_license';
    case IDENTITY_CARD = 'identity_card';
    case INTERNAL_PASSPORT = 'internal_passport';
    case ADDRESS = 'address';
    case UTILITY_BILL = 'utility_bill';
    case BANK_STATEMENT = 'bank_statement';
    case RENTAL_AGREEMENT = 'rental_agreement';
    case PASSPORT_REGISTRATION = 'passport_registration';
    case TEMPORARY_REGISTRATION = 'temporary_registration';
    case PHONE_NUMBER = 'phone_number';
    case EMAIL = 'email';
}
