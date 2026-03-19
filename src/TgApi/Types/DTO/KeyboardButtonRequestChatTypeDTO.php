<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object defines the criteria used to request a suitable chat. Information about the selected chat will be shared with the bot when the corresponding button is pressed. The bot will be granted requested rights in the chat if appropriate. [More about requesting chats »](https://core.telegram.org/bots/features#chat-and-user-selection).')]
#[See('https://core.telegram.org/bots/api#keyboardbuttonrequestchat')]
class KeyboardButtonRequestChatTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Signed 32-bit identifier of the request, which will be received back in the [ChatShared](https://core.telegram.org/bots/api#chatshared) object. Must be unique within the message')]
        public int $requestId,
        #[Description('Pass _True_ to request a channel chat, pass _False_ to request a group or a supergroup chat.')]
        public bool $chatIsChannel,
        #[Description('Pass _True_ to request a forum supergroup, pass _False_ to request a non-forum chat. If not specified, no additional restrictions are applied.')]
        public ?bool $chatIsForum = null,
        #[Description('Pass _True_ to request a supergroup or a channel with a username, pass _False_ to request a chat without a username. If not specified, no additional restrictions are applied.')]
        public ?bool $chatHasUsername = null,
        #[Description('Pass _True_ to request a chat owned by the user. Otherwise, no additional restrictions are applied.')]
        public ?bool $chatIsCreated = null,
        #[Description('An object listing the required administrator rights of the user in the chat. The rights must be a superset of _bot\_administrator\_rights_. If not specified, no additional restrictions are applied.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatAdministratorRightsTypeDTO $userAdministratorRights = null,
        #[Description('An object listing the required administrator rights of the bot in the chat. The rights must be a subset of _user\_administrator\_rights_. If not specified, no additional restrictions are applied.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\ChatAdministratorRightsTypeDTO $botAdministratorRights = null,
        #[Description('Pass _True_ to request a chat with the bot as a member. Otherwise, no additional restrictions are applied.')]
        public ?bool $botIsMember = null,
        #[Description('Pass _True_ to request the chat"s title')]
        public ?bool $requestTitle = null,
        #[Description('Pass _True_ to request the chat"s username')]
        public ?bool $requestUsername = null,
        #[Description('Pass _True_ to request the chat"s photo')]
        public ?bool $requestPhoto = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::KeyboardButtonRequestChat;
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
{"request_id":{"property":"requestId","tgPropName":"request_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"chat_is_channel":{"property":"chatIsChannel","tgPropName":"chat_is_channel","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":false,"required":true},"chat_is_forum":{"property":"chatIsForum","tgPropName":"chat_is_forum","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"chat_has_username":{"property":"chatHasUsername","tgPropName":"chat_has_username","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"chat_is_created":{"property":"chatIsCreated","tgPropName":"chat_is_created","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"user_administrator_rights":{"property":"userAdministratorRights","tgPropName":"user_administrator_rights","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatAdministratorRightsTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatAdministratorRights"}],"nullable":true,"required":false},"bot_administrator_rights":{"property":"botAdministratorRights","tgPropName":"bot_administrator_rights","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\ChatAdministratorRightsTypeDTO"],"tgTypes":[{"type":"api-type","name":"ChatAdministratorRights"}],"nullable":true,"required":false},"bot_is_member":{"property":"botIsMember","tgPropName":"bot_is_member","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_title":{"property":"requestTitle","tgPropName":"request_title","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_username":{"property":"requestUsername","tgPropName":"request_username","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"request_photo":{"property":"requestPhoto","tgPropName":"request_photo","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
