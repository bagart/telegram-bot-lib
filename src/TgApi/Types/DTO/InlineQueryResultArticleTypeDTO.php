<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;
use BAGArt\TelegramBot\TgApiServices\TgApiProperty;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Represents a link to an article or web page.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultarticle')]
class InlineQueryResultArticleTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiEntityEnumContract $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct(
        #[Description('Unique identifier for this result, 1-64 Bytes')]
        public string $id,
        #[Description('Title of the result')]
        public string $title,
        #[Description('Content of the message to be sent')]
        public InputMessageContentTypeDTO $inputMessageContent,
        #[Description('Type of the result, must be _article_')]
        public string $type = 'article',
        #[Description('[Inline keyboard](https://core.telegram.org/bots/features#inline-keyboards) attached to the message')]
        public ?InlineKeyboardMarkupTypeDTO $replyMarkup = null,
        #[Description('URL of the result')]
        public ?string $url = null,
        #[Description('Short description of the result')]
        public ?string $description = null,
        #[Description('Url of the thumbnail for the result')]
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
        return TgApiTypesEnum::InlineQueryResultArticle;
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
{"type":{"property":"type","tgPropName":"type","types":["string"],"tgTypes":[{"type":"str","literal":"article"}],"nullable":false,"required":true},"id":{"property":"id","tgPropName":"id","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"title":{"property":"title","tgPropName":"title","types":["string"],"tgTypes":[{"type":"str"}],"nullable":false,"required":true},"input_message_content":{"property":"inputMessageContent","tgPropName":"input_message_content","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InputMessageContentTypeDTO"],"tgTypes":[{"type":"api-type","name":"InputMessageContent"}],"nullable":false,"required":true},"reply_markup":{"property":"replyMarkup","tgPropName":"reply_markup","types":["\\BAGArt\\TelegramBot\\TgApi\\Types\\DTO\\InlineKeyboardMarkupTypeDTO"],"tgTypes":[{"type":"api-type","name":"InlineKeyboardMarkup"}],"nullable":true,"required":false},"url":{"property":"url","tgPropName":"url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"description":{"property":"description","tgPropName":"description","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"thumbnail_url":{"property":"thumbnailUrl","tgPropName":"thumbnail_url","types":["string"],"tgTypes":[{"type":"str"}],"nullable":true,"required":false},"thumbnail_width":{"property":"thumbnailWidth","tgPropName":"thumbnail_width","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false},"thumbnail_height":{"property":"thumbnailHeight","tgPropName":"thumbnail_height","types":["int"],"tgTypes":[{"type":"int32"}],"nullable":true,"required":false}}
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
