<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\InlineQueryResultDocumentPropMimeTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a link to a file. By default, this file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the file. Currently, only **.PDF** and **.ZIP** files can be sent using this method.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultdocument')]
class InlineQueryResultDocumentTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 bytes')]
        public string $id,
        #[Description('Title for the result')]
        public string $title,
        #[Description('A valid URL for the file')]
        public string $documentUrl,
        #[Description('MIME type of the content of the file, either “application/pdf” or “application/zip”')]
        public InlineQueryResultDocumentPropMimeTypeEnum $mimeType,
        #[Description('Type of the result, must be _document_')]
        public string $type = 'document',
        #[Description('Caption of the document to be sent, 0-1024 characters after entities parsing')]
        public ?string $caption = null,
        #[Description('Mode for parsing entities in the document caption. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in the caption, which can be specified instead of _parse\_mode_')]
        public ?array $captionEntities = null,
        #[Description('Short description of the result')]
        public ?string $description = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the file')]
        public ?InputMessageContentTypeDTO $inputMessageContent = null,
        #[Description('URL of the thumbnail (JPEG only) for the file')]
        public ?string $thumbnailUrl = null,
        #[Description('Thumbnail width')]
        public ?int $thumbnailWidth = null,
        #[Description('Thumbnail height')]
        public ?int $thumbnailHeight = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultDocument;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"document"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"document_url":{"property":"documentUrl","tgPropName":"document_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"mime_type":{"property":"mimeType","tgPropName":"mime_type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\InlineQueryResultDocumentPropMimeTypeEnum"],"tgTypes":[{"type":"str","literal":"application\/pdf"},{"type":"str","literal":"application\/zip"}],"nullable":false,"required":true},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"thumbnail_width":{"property":"thumbnailWidth","tgPropName":"thumbnail_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"thumbnail_height":{"property":"thumbnailHeight","tgPropName":"thumbnail_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
