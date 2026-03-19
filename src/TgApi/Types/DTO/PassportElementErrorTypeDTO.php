<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents an error in the Telegram Passport element which was submitted that should be resolved by the user. It should be one of:; ; -   [PassportElementErrorDataField](https://core.telegram.org/bots/api#passportelementerrordatafield); -   [PassportElementErrorFrontSide](https://core.telegram.org/bots/api#passportelementerrorfrontside); -   [PassportElementErrorReverseSide](https://core.telegram.org/bots/api#passportelementerrorreverseside); -   [PassportElementErrorSelfie](https://core.telegram.org/bots/api#passportelementerrorselfie); -   [PassportElementErrorFile](https://core.telegram.org/bots/api#passportelementerrorfile); -   [PassportElementErrorFiles](https://core.telegram.org/bots/api#passportelementerrorfiles); -   [PassportElementErrorTranslationFile](https://core.telegram.org/bots/api#passportelementerrortranslationfile); -   [PassportElementErrorTranslationFiles](https://core.telegram.org/bots/api#passportelementerrortranslationfiles); -   [PassportElementErrorUnspecified](https://core.telegram.org/bots/api#passportelementerrorunspecified)')]
#[See('https://core.telegram.org/bots/api#passportelementerror')]
class PassportElementErrorTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiTypesEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::PassportElementError;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
