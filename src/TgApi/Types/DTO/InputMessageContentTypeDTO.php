<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is oneOf contract. Not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents the content of a message to be sent as a result of an inline query. Telegram clients currently support the following 5 types:; ; -   [InputTextMessageContent](https://core.telegram.org/bots/api#inputtextmessagecontent); -   [InputLocationMessageContent](https://core.telegram.org/bots/api#inputlocationmessagecontent); -   [InputVenueMessageContent](https://core.telegram.org/bots/api#inputvenuemessagecontent); -   [InputContactMessageContent](https://core.telegram.org/bots/api#inputcontactmessagecontent); -   [InputInvoiceMessageContent](https://core.telegram.org/bots/api#inputinvoicemessagecontent)')]
#[See('https://core.telegram.org/bots/api#inputmessagecontent')]
class InputMessageContentTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::InputMessageContent;
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
