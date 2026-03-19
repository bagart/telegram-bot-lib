<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\TgApi\Types\DTO\PreparedInlineMessageTypeDTO;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Stores a message that can be sent by a user of a Mini App. Returns a [PreparedInlineMessage](https://core.telegram.org/bots/api#preparedinlinemessage) object.')]
#[See('https://core.telegram.org/bots/api#savepreparedinlinemessage')]
class SavePreparedInlineMessageMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier of the target user that can use the prepared message')]
        public int $userId,
        #[Description('An object describing the message to be sent')]
        public \BAGArt\TelegramBot\TgApi\Types\DTO\InlineQueryResultTypeDTO $result,
        #[Description('Pass _True_ if the message can be sent to private chats with users')]
        public ?bool $allowUserChats = null,
        #[Description('Pass _True_ if the message can be sent to private chats with bots')]
        public ?bool $allowBotChats = null,
        #[Description('Pass _True_ if the message can be sent to group and supergroup chats')]
        public ?bool $allowGroupChats = null,
        #[Description('Pass _True_ if the message can be sent to channel chats')]
        public ?bool $allowChannelChats = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            PreparedInlineMessageTypeDTO::class,
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::savePreparedInlineMessage;
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
{"user_id":{"property":"userId","tgPropName":"user_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"result":{"property":"result","tgPropName":"result","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineQueryResultTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineQueryResult"}],"nullable":false,"required":true},"allow_user_chats":{"property":"allowUserChats","tgPropName":"allow_user_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_bot_chats":{"property":"allowBotChats","tgPropName":"allow_bot_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_group_chats":{"property":"allowGroupChats","tgPropName":"allow_group_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"allow_channel_chats":{"property":"allowChannelChats","tgPropName":"allow_channel_chats","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
