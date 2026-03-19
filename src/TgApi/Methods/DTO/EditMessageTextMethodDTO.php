<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\TgApi\Methods\Enum\ParseModeEnum;
use BAGArt\TelegramBot\TgApi\Methods\TgApiMethodsEnum;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\LinkPreviewOptionsTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Use this method to edit text and [game](https://core.telegram.org/bots/api#games) messages. On success, if the edited message is not an inline message, the edited [Message](https://core.telegram.org/bots/api#message) is returned, otherwise _True_ is returned. Note that business messages that were not sent by the bot and do not contain an inline keyboard can only be edited within **48 hours** from the time they were sent.')]
#[See('https://core.telegram.org/bots/api#editmessagetext')]
class EditMessageTextMethodDTO implements TgApiMethodDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('New text of the message, 1-4096 characters after entities parsing')]
        public string $text,
        #[Description('Unique identifier of the business connection on behalf of which the message to be edited was sent')]
        public ?string $businessConnectionId = null,
        #[Description('Required if _inline\_message\_id_ is not specified. Unique identifier for the target chat or username of the target channel (in the format `@channelusername`)')]
        public ?string $chatId = null,
        #[Description('Required if _inline\_message\_id_ is not specified. Identifier of the message to edit')]
        public ?int $messageId = null,
        #[Description('Required if _chat\_id_ and _message\_id_ are not specified. Identifier of the inline message')]
        public ?string $inlineMessageId = null,
        #[Description('Mode for parsing entities in the message text. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?ParseModeEnum $parseMode = null,
        #[Description('An array of special entities that appear in message text, which can be specified instead of _parse\_mode_')]
        public ?array $entities = null,
        #[Description('Link preview generation options for the message')]
        public ?LinkPreviewOptionsTypeDTO $linkPreviewOptions = null,
        #[Description('An object for an [inline keyboard](https://core.telegram.org/bots/features#inline-keyboards).')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function getReturnTypes(): array
    {
        return [
            MessageTypeDTO::class,
            'bool',
        ];
    }

    public static function tgApiEntity(): TgApiMethodsEnum
    {
        return TgApiMethodsEnum::editMessageText;
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
{"business_connection_id":{"property":"businessConnectionId","tgPropName":"business_connection_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"chat_id":{"property":"chatId","tgPropName":"chat_id","types":["string"],"tgTypes":[{"type":"int32"},{"type":"str"}],"nullable":true,"required":false},"message_id":{"property":"messageId","tgPropName":"message_id","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"inline_message_id":{"property":"inlineMessageId","tgPropName":"inline_message_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"text":{"property":"text","tgPropName":"text","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Methods\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"entities":{"property":"entities","tgPropName":"entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"link_preview_options":{"property":"linkPreviewOptions","tgPropName":"link_preview_options","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\LinkPreviewOptionsTypeDTO"],"tgTypes":[{"type":"api-type","name":"LinkPreviewOptions"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false}}
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
