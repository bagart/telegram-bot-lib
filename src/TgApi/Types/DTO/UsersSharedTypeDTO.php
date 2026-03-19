<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about the users whose identifiers were shared with the bot using a [KeyboardButtonRequestUsers](https://core.telegram.org/bots/api#keyboardbuttonrequestusers) button.')]
#[See('https://core.telegram.org/bots/api#usersshared')]
class UsersSharedTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the request')]
        public int $requestId,
        #[Description('Information about users shared with the bot.')]
        public array $users,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::UsersShared;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    /** @return TgApiProperty[] */
    public static function tgPropertyMetas(): array
    {
        $metaByProp = json_decode(
            <<<'XJSON'
{"request_id":{"property":"requestId","tgPropName":"request_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"users":{"property":"users","tgPropName":"users","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\SharedUserTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"SharedUser"}}],"nullable":false,"required":true}}
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
