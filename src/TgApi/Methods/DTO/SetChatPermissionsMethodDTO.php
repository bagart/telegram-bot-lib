<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatPermissionsTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to set default chat permissions for all members. The bot must be an administrator in the group or a supergroup for this to work and must have the _can\_restrict\_members_ administrator rights. Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#setchatpermissions')]
class SetChatPermissionsMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target supergroup (in the format `@supergroupusername`)')]
        public string $chatId,
        #[Description('An object for new default chat permissions')]
        public ChatPermissionsTypeDTO $permissions,
        #[Description('Pass _True_ if chat permissions are set independently. Otherwise, the _can\_send\_other\_messages_ and _can\_add\_web\_page\_previews_ permissions will imply the _can\_send\_messages_, _can\_send\_audios_, _can\_send\_documents_, _can\_send\_photos_, _can\_send\_videos_, _can\_send\_video\_notes_, and _can\_send\_voice\_notes_ permissions; the _can\_send\_polls_ permission will imply the _can\_send\_messages_ permission.')]
        public ?bool $useIndependentChatPermissions = null,
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
        return TgApiMethodsEnum::setChatPermissions;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"permissions":{"property":"permissions","tgPropName":"permissions","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatPermissionsTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatPermissions"}],"nullable":false,"required":true},"use_independent_chat_permissions":{"property":"useIndependentChatPermissions","tgPropName":"use_independent_chat_permissions","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
