<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object contains information about a user that was shared with the bot using a [KeyboardButtonRequestUsers](https://core.telegram.org/bots/api#keyboardbuttonrequestusers) button.')]
#[See('https://core.telegram.org/bots/api#shareduser')]
class SharedUserTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the shared user.  The bot may not have access to the user and could be unable to use this identifier, unless the user is already known to the bot by some other means.')]
        public string $userId,
        #[Description('First name of the user, if the name was requested by the bot')]
        public ?string $firstName = null,
        #[Description('Last name of the user, if the name was requested by the bot')]
        public ?string $lastName = null,
        #[Description('Username of the user, if the username was requested by the bot')]
        public ?string $username = null,
        #[Description('Available sizes of the chat photo, if the photo was requested by the bot')]
        public ?array $photo = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::SharedUser;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["string"],"tgTypes":[{"type":"int53"}],"nullable":false,"required":true},"first_name":{"property":"firstName","tgPropName":"first_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"last_name":{"property":"lastName","tgPropName":"last_name","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"username":{"property":"username","tgPropName":"username","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"photo":{"property":"photo","tgPropName":"photo","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\PhotoSizeTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"PhotoSize"}}],"nullable":true,"required":false}}
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
