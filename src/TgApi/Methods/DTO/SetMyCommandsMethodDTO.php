<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\BotCommandScopeTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to change the list of the bot"s commands. See [this manual](https://core.telegram.org/bots/features#commands) for more details about bot commands. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#setmycommands')]
class SetMyCommandsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('An array of bot commands to be set as the list of the bot"s commands. At most 100 commands can be specified.')]
        public array $commands,
        #[Description('An object, describing scope of users for which the commands are relevant. Defaults to [BotCommandScopeDefault](https://core.telegram.org/bots/api#botcommandscopedefault).')]
        public ?BotCommandScopeTypeDTO $scope = null,
        #[Description('A two-letter ISO 639-1 language code. If empty, commands will be applied to all users from the given scope, for whose language there are no dedicated commands')]
        public ?string $languageCode = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::setMyCommands;
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
{"commands":{"property":"commands","tgPropName":"commands","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BotCommandTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"BotCommand"}}],"nullable":false,"required":true},"scope":{"property":"scope","tgPropName":"scope","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BotCommandScopeTypeDTO"],"tgTypes":[{"type":"api-type","name":"BotCommandScope"}],"nullable":true,"required":false},"language_code":{"property":"languageCode","tgPropName":"language_code","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false}}
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
