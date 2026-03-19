<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('A simple method for testing your bot"s authentication token. Requires no parameters. Returns basic information about the bot in form of a [User](https://core.telegram.org/bots/api#user) object.')]
#[See('https://core.telegram.org/bots/api#getme')]
class GetMeMethodDTO implements TgApiMethodDTOContract
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
        return TgApiMethodsEnum::getMe;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    public static function getReturnTypes(): array
    {
        return [
            UserTypeDTO::class,
        ];
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
