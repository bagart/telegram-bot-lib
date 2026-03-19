<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object defines the criteria used to request suitable users. Information about the selected users will be shared with the bot when the corresponding button is pressed. [More about requesting users »](https://core.telegram.org/bots/features#chat-and-user-selection)')]
#[See('https://core.telegram.org/bots/api#keyboardbuttonrequestusers')]
class KeyboardButtonRequestUsersTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Signed 32-bit identifier of the request that will be received back in the [UsersShared](https://core.telegram.org/bots/api#usersshared) object. Must be unique within the message')]
        public int $requestId,
        #[Description('Pass _True_ to request bots, pass _False_ to request regular users. If not specified, no additional restrictions are applied.')]
        public ?bool $userIsBot = null,
        #[Description('Pass _True_ to request premium users, pass _False_ to request non-premium users. If not specified, no additional restrictions are applied.')]
        public ?bool $userIsPremium = null,
        #[Description('The maximum number of users to be selected; 1-10. Defaults to 1.')]
        public ?int $maxQuantity = null,
        #[Description('Pass _True_ to request the users" first and last names')]
        public ?bool $requestName = null,
        #[Description('Pass _True_ to request the users" usernames')]
        public ?bool $requestUsername = null,
        #[Description('Pass _True_ to request the users" photos')]
        public ?bool $requestPhoto = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::KeyboardButtonRequestUsers;
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
{"request_id":{"property":"requestId","tgPropName":"request_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"user_is_bot":{"property":"userIsBot","tgPropName":"user_is_bot","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"user_is_premium":{"property":"userIsPremium","tgPropName":"user_is_premium","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"max_quantity":{"property":"maxQuantity","tgPropName":"max_quantity","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"request_name":{"property":"requestName","tgPropName":"request_name","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_username":{"property":"requestUsername","tgPropName":"request_username","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_photo":{"property":"requestPhoto","tgPropName":"request_photo","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
