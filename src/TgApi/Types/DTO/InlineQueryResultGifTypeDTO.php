<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ParseModeEnum;
use BAGArt\TelegramBot\TgApi\Types\Enum\ThumbnailMimeTypeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a link to an animated GIF file. By default, this animated GIF file will be sent by the user with optional caption. Alternatively, you can use _input\_message\_content_ to send a message with the specified content instead of the animation.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultgif')]
class InlineQueryResultGifTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 bytes')]
        public string $id,
        #[Description('A valid URL for the GIF file')]
        public string $gifUrl,
        #[Description('URL of the static (JPEG or GIF) or animated (MPEG4) thumbnail for the result')]
        public string $thumbnailUrl,
        #[Description('Type of the result, must be _gif_')]
        public string $type = 'gif',
        #[Description('Width of the GIF')]
        public ?int $gifWidth = null,
        #[Description('Height of the GIF')]
        public ?int $gifHeight = null,
        #[Description('Duration of the GIF in seconds')]
        public ?int $gifDuration = null,
        #[Description('MIME type of the thumbnail, must be one of “image/jpeg”, “image/gif”, or “video/mp4”. Defaults to “image/jpeg”')]
        public ?ThumbnailMimeTypeEnum $thumbnailMimeType = null,
        #[Description('Title for the result')]
        public ?string $title = null,
        #[Description('Caption of the GIF file to be sent, 0-1024 characters after entities parsing')]
        public ?string $caption = null,
        #[Description('Mode for parsing entities in the caption. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
        public ?ParseModeEnum $parseMode = null,
        #[Description('List of special entities that appear in the caption, which can be specified instead of _parse\_mode_')]
        public ?array $captionEntities = null,
        #[Description('Pass _True_, if the caption must be shown above the message media')]
        public ?bool $showCaptionAboveMedia = null,
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('Content of the message to be sent instead of the GIF animation')]
        public ?InputMessageContentTypeDTO $inputMessageContent = null,
    ) {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResultGif;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"gif"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"gif_url":{"property":"gifUrl","tgPropName":"gif_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"gif_width":{"property":"gifWidth","tgPropName":"gif_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"gif_height":{"property":"gifHeight","tgPropName":"gif_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"gif_duration":{"property":"gifDuration","tgPropName":"gif_duration","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"thumbnail_mime_type":{"property":"thumbnailMimeType","tgPropName":"thumbnail_mime_type","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ThumbnailMimeTypeEnum"],"tgTypes":[{"type":"str","literal":"image\/jpeg"},{"type":"str","literal":"image\/gif"},{"type":"str","literal":"video\/mp4"}],"nullable":true,"required":false},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"caption":{"property":"caption","tgPropName":"caption","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"parse_mode":{"property":"parseMode","tgPropName":"parse_mode","types":["?\\BAGArt\\TelegramBot\\TgApi\\Types\\Enum\\ParseModeEnum"],"tgTypes":[{"type":"str","literal":"HTML"},{"type":"str","literal":"MarkdownV2"},{"type":"str","literal":"Markdown"}],"nullable":true,"required":false},"caption_entities":{"property":"captionEntities","tgPropName":"caption_entities","types":[["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\MessageEntityTypeDTO"]],"tgTypes":[{"type":"array","of":{"type":"api-type","name":"MessageEntity"}}],"nullable":true,"required":false},"show_caption_above_media":{"property":"showCaptionAboveMedia","tgPropName":"show_caption_above_media","types":["bool"],"tgTypes":[{"type":"bool"}],"nullable":true,"required":false},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":true,"required":false}}
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
