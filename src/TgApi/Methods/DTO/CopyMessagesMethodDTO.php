<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageIdTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to copy messages of any kind. If some of the specified messages can"t be found or copied, they are skipped. Service messages, paid media messages, giveaway messages, giveaway winners messages, and invoice messages can"t be copied. A quiz [poll](https://core.telegram.org/bots/api#poll) can be copied only if the value of the field _correct\_option\_id_ is known to the bot. The method is analogous to the method [forwardMessages](https://core.telegram.org/bots/api#forwardmessages), but the copied messages don"t have a link to the original message. Album grouping is kept for copied messages. On success, an array of [MessageId](https://core.telegram.org/bots/api#messageid) of the sent messages is returned.')]
#[See('https://core.telegram.org/bots/api#copymessages')]
class CopyMessagesMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public string $chatId,
        #[Description('Unique identifier for the chat where the original messages were sent (or channel username in the format `@channelusername`)')]
        public string $fromChatId,
        #[Description('An array of 1-100 identifiers of messages in the chat _from\_chat\_id_ to copy. The identifiers must be specified in a strictly increasing order.')]
        public array $messageIds,
        #[Description('Unique identifier for the target message thread (topic) of a forum; for forum supergroups and private chats of bots with forum topic mode enabled only')]
        public ?int $messageThreadId = null,
        #[Description('Identifier of the direct messages topic to which the messages will be sent; required if the messages are sent to a direct messages chat')]
        public ?int $directMessagesTopicId = null,
        #[Description('Sends the messages [silently](https://telegram.org/blog/channels-2-0#silent-messages). Users will receive a notification with no sound.')]
        public ?bool $disableNotification = null,
        #[Description('Protects the contents of the sent messages from forwarding and saving')]
        public ?bool $protectContent = null,
        #[Description('Pass _True_ to copy the messages without their captions')]
        public ?bool $removeCaption = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            [
                MessageIdTypeDTO::class,
            ],
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::copyMessages;
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
{"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_thread_id":{"property":"messageThreadId","tgPropName":"message_thread_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"direct_messages_topic_id":{"property":"directMessagesTopicId","tgPropName":"direct_messages_topic_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"from_chat_id":{"property":"fromChatId","tgPropName":"from_chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":false,"required":true},"message_ids":{"property":"messageIds","tgPropName":"message_ids","types":[["int"]],"tgTypes":[{"type":"array","of":{"type":"int32"}}],"nullable":false,"required":true},"disable_notification":{"property":"disableNotification","tgPropName":"disable_notification","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"protect_content":{"property":"protectContent","tgPropName":"protect_content","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"remove_caption":{"property":"removeCaption","tgPropName":"remove_caption","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false}}
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
