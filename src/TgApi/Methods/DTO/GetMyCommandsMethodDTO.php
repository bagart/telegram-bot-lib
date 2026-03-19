<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to get the current list of the bot"s commands for the given scope and user language. Returns an Array of [BotCommand](https://core.telegram.org/bots/api#botcommand) objects. If commands aren"t set, an empty list is returned.')]
#[See('https://core.telegram.org/bots/api#getmycommands')]
class GetMyCommandsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('An object, describing scope of users. Defaults to [BotCommandScopeDefault](https://core.telegram.org/bots/api#botcommandscopedefault).')]
        public ?BotCommandScopeTypeDTO $scope = null,
        #[Description('A two-letter ISO 639-1 language code or an empty string')]
        public ?string $languageCode = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            [
                BotCommandTypeDTO::class,
            ],
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::getMyCommands;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Method;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"scope":{"property":"scope","tgPropName":"scope","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BotCommandScopeTypeDTO"],"tgTypes":[{"type":"api-type","name":"BotCommandScope"}],"nullable":true,"required":false},"language_code":{"property":"languageCode","tgPropName":"language_code","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
XJSON,
            true,
            20,
            JSON_THROW_ON_ERROR
        );

        $result = [];
        foreach ($metaByProp as $tgPropName => $propertyMeta) {
            $result[$tgPropName] = new TgApiProperty(...$propertyMeta);
        }

        return $result;
    }
}
