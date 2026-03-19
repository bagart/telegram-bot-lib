<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\GiftsTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Returns the list of gifts that can be sent by the bot to users and channel chats. Requires no parameters. Returns a [Gifts](https://core.telegram.org/bots/api#gifts) object.')]
#[See('https://core.telegram.org/bots/api#getavailablegifts')]
class GetAvailableGiftsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiMethodsEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::getAvailableGifts;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    public static function getReturnTypes(): array
    {
        return [
            GiftsTypeDTO::class,
        ];
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
