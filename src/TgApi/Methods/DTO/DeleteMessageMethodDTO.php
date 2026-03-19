<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to delete a message, including service messages, with the following limitations:; ; \- A message can only be deleted if it was sent less than 48 hours ago.; ; \- Service messages about a supergroup, channel, or forum topic creation can"t be deleted.; ; \- A dice message in a private chat can only be deleted if it was sent more than 24 hours ago.; ; \- Bots can delete outgoing messages in private chats, groups, and supergroups.; ; \- Bots can delete incoming messages in private chats.; ; \- Bots granted _can\_post\_messages_ permissions can delete outgoing messages in channels.; ; \- If the bot is an administrator of a group, it can delete any message there.; ; \- If the bot has _can\_delete\_messages_ administrator right in a supergroup or a channel, it can delete any message there.; ; \- If the bot has _can\_manage\_direct\_messages_ administrator right in a channel, it can delete any message in the corresponding direct messages chat.; ; Returns _True_ on success.')]
#[See('https://core.telegram.org/bots/api#deletemessage')]
class DeleteMessageMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
        #[Description('Identifier of the message to delete')]
        public int $messageId,
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
        return TgApiMethodsEnum::deleteMessage;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true}}
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
