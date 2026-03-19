<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is contract but not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents the content of a media message to be sent. It should be one of; ; -   [InputMediaAnimation](https://core.telegram.org/bots/api#inputmediaanimation); -   [InputMediaDocument](https://core.telegram.org/bots/api#inputmediadocument); -   [InputMediaAudio](https://core.telegram.org/bots/api#inputmediaaudio); -   [InputMediaPhoto](https://core.telegram.org/bots/api#inputmediaphoto); -   [InputMediaVideo](https://core.telegram.org/bots/api#inputmediavideo)')]
#[See('https://core.telegram.org/bots/api#inputmedia')]
class InputMediaTypeDTO implements TgApiTypeDTOContract
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
        return TgApiTypesEnum::InputMedia;
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
