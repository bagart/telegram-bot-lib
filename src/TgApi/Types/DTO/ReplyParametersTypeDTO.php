<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\QuoteParseModeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Describes reply parameters for the message that is being sent.')]
#[See('https://core.telegram.org/bots/api#replyparameters')]
class ReplyParametersTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Identifier of the message that will be replied to in the current chat, or in the chat _chat\_id_ if it is specified')]
        public int $messageId,
        #[Description('If the message to be replied to is from a different chat, unique identifier for the chat or username of the channel (in the format `@channelusername`). Not supported for messages sent on behalf of a business account and messages from channel direct messages chats.')]
        public ?string $chatId = null,
        #[Description('Pass _True_ if the message should be sent even if the specified message to be replied to is not found. Always _False_ for replies in another chat or forum topic. Always _True_ for messages sent on behalf of a business account.')]
        public ?bool $allowSendingWithoutReply = null,
        #[Description('Quoted part of the message to be replied to; 0-1024 characters after entities parsing. The quote must be an exact substring of the message to be replied to, including _bold_, _italic_, _underline_, _strikethrough_, _spoiler_, and _custom\_emoji_ entities. The message will fail to send if the quote isn"t found in the original message.')]
        public ?string $quote = null,
        #[Description('Mode for parsing entities in the quote. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?QuoteParseModeEnum $quoteParseMode = null,
        #[Description('An array of special entities that appear in the quote. It can be specified instead of _quote\_parse\_mode_.')]
        public ?array $quoteEntities = null,
        #[Description('Position of the quote in the original message in UTF-16 code units')]
        public ?int $quotePosition = null,
        #[Description('Identifier of the specific checklist task to be replied to')]
        public ?int $checklistTaskId = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::ReplyParameters;
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
{"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":false,"required":true},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":true,"required":false},"allow_sending_without_reply":{"property":"allowSendingWithoutReply","tgPropName":"allow_sending_without_reply","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"quote":{"property":"quote","tgPropName":"quote","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"quote_parse_mode":{"property":"quoteParseMode","tgPropName":"quote_parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\QuoteParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"quote_entities":{"property":"quoteEntities","tgPropName":"quote_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"quote_position":{"property":"quotePosition","tgPropName":"quote_position","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"checklist_task_id":{"property":"checklistTaskId","tgPropName":"checklist_task_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
