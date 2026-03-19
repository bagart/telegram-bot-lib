<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatAdministratorRightsTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to change the default administrator rights requested by the bot when it"s added as an administrator to groups or channels. These rights will be suggested to users, but they are free to modify the list before adding the bot. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#setmydefaultadministratorrights')]
class SetMyDefaultAdministratorRightsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('An object describing new default administrator rights. If not specified, the default administrator rights will be cleared.')]
        public ?ChatAdministratorRightsTypeDTO $rights = null,
        #[Description('Pass _True_ to change the default administrator rights of the bot in channels. Otherwise, the default administrator rights of the bot for groups and supergroups will be changed.')]
        public ?bool $forChannels = null,
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
        return TgApiMethodsEnum::setMyDefaultAdministratorRights;
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
{"rights":{"property":"rights","tgPropName":"rights","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatAdministratorRightsTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatAdministratorRights"}],"nullable":true,"required":false},"for_channels":{"property":"forChannels","tgPropName":"for_channels","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
