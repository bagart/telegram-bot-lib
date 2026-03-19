<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a link to a page containing an embedded video player or a video file. By default, this video file will be sent by the user with an optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the video.; ; > If an InlineQueryResultVideo message contains an embedded video (e.g., YouTube), you **must** replace its content using _input\_message\_content_.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultvideo')]
class InlineQueryResultVideoTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 bytes')]
        public string $id,
        #[Description('A valid URL for the embedded video player or video file')]
        public string $videoUrl,
        #[Description('MIME type of the content of the video URL, “text/html” or “video/mp4”')]
        public \BAGArt\TelegramBot\TgApi\Types\Enum\InlineQueryResultVideoPropMimeTypeEnum $mimeType,
        #[Description('URL of the thumbnail (JPEG only) for the video')]
        public string $thumbnailUrl,
        #[Description('Title for the result')]
        public string $title,
        #[Description('Type of the result, must be _video_')]
        public string $type = 'video',
        #[Description('Caption of the video to be sent, 0-1024 characters after entities parsing')]
        public ?string $caption = null,
        #[Description('Mode for parsing entities in the video caption. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?\BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in the caption, which can be specified instead of _parse\_mode_')]
        public ?array $captionEntities = null,
        #[Description('Pass _True_, if the caption must be shown above the message media')]
        public ?bool $showCaptionAboveMedia = null,
        #[Description('Video width')]
        public ?int $videoWidth = null,
        #[Description('Video height')]
        public ?int $videoHeight = null,
        #[Description('Video duration in seconds')]
        public ?int $videoDuration = null,
        #[Description('Short description of the result')]
        public ?string $description = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the video. This field is **required** if InlineQueryResultVideo is used to send an HTML-page as a result (e.g., a YouTube video).')]
        public ?\BAGArt\TelegramBot\TgApi\Types\DTO\InputMessageContentTypeDTO $inputMessageContent = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultVideo;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"video"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"video_url":{"property":"videoUrl","tgPropName":"video_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"mime_type":{"property":"mimeType","tgPropName":"mime_type","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\InlineQueryResultVideoPropMimeTypeEnum"],"tgTypes":[{"type":"str","literal":"text\/html"},{"type":"str","literal":"video\/mp4"}],"nullable":false,"required":true},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"show_caption_above_media":{"property":"showCaptionAboveMedia","tgPropName":"show_caption_above_media","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"video_width":{"property":"videoWidth","tgPropName":"video_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"video_height":{"property":"videoHeight","tgPropName":"video_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"video_duration":{"property":"videoDuration","tgPropName":"video_duration","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false}}
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
