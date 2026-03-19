<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes the connection of the bot with a business account.')]
#[See('https://core.telegram.org/bots/api#businessconnection')]
class BusinessConnectionTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the business connection')]
        public string $id,
        #[Description('Business account user that created the business connection')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO $user,
        #[Description('Identifier of a private chat with the user who created the business connection.')]
        public string $userChatId,
        #[Description('Date the connection was established in Unix time')]
        public int $date,
        #[Description('_True_, if the connection is active')]
        public bool $isEnabled,
        #[Description('Rights of the business bot')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\BusinessBotRightsTypeDTO $rights = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::BusinessConnection;
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
{"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"user":{"property":"user","tgPropName":"user","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\UserTypeDTO"],"tgTypes":[{"type":"api-type","name":"User"}],"nullable":false,"required":true},"user_chat_id":{"property":"userChatId","tgPropName":"user_chat_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"date":{"property":"date","tgPropName":"date","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"rights":{"property":"rights","tgPropName":"rights","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\BusinessBotRightsTypeDTO"],"tgTypes":[{"type":"api-type","name":"BusinessBotRights"}],"nullable":true,"required":false},"is_enabled":{"property":"isEnabled","tgPropName":"is_enabled","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true}}
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
