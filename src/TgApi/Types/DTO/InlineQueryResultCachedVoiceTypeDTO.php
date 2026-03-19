<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a link to a voice message stored on the Telegram servers. By default, this voice message will be sent by the user. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the voice message.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultcachedvoice')]
class InlineQueryResultCachedVoiceTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 bytes')]
        public string $id,
        #[Description('A valid file identifier for the voice message')]
        public string $voiceFileId,
        #[Description('Voice message title')]
        public string $title,
        #[Description('Type of the result, must be _voice_')]
        public string $type = 'voice',
        #[Description('Caption, 0-1024 characters after entities parsing')]
        public ?string $caption = null,
        #[Description('Mode for parsing entities in the voice message caption. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in the caption, which can be specified instead of _parse\_mode_')]
        public ?array $captionEntities = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the voice message')]
        public ?InputMessageContentTypeDTO $inputMessageContent = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultCachedVoice;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"voice"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"voice_file_id":{"property":"voiceFileId","tgPropName":"voice_file_id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false}}
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
