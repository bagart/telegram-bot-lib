<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\DTO;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\TgApiEntityScopeEnum;
use BAGArt\TelegramBot\TgApi\Types\TgApiTypesEnum;

#[Todo('Is contract but not implemented yet.')]
#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('This object represents one result of an inline query. Telegram clients currently support results of the following 20 types:; ; -   [InlineQueryResultCachedAudio](https://core.telegram.org/bots/api#inlinequeryresultcachedaudio); -   [InlineQueryResultCachedDocument](https://core.telegram.org/bots/api#inlinequeryresultcacheddocument); -   [InlineQueryResultCachedGif](https://core.telegram.org/bots/api#inlinequeryresultcachedgif); -   [InlineQueryResultCachedMpeg4Gif](https://core.telegram.org/bots/api#inlinequeryresultcachedmpeg4gif); -   [InlineQueryResultCachedPhoto](https://core.telegram.org/bots/api#inlinequeryresultcachedphoto); -   [InlineQueryResultCachedSticker](https://core.telegram.org/bots/api#inlinequeryresultcachedsticker); -   [InlineQueryResultCachedVideo](https://core.telegram.org/bots/api#inlinequeryresultcachedvideo); -   [InlineQueryResultCachedVoice](https://core.telegram.org/bots/api#inlinequeryresultcachedvoice); -   [InlineQueryResultArticle](https://core.telegram.org/bots/api#inlinequeryresultarticle); -   [InlineQueryResultAudio](https://core.telegram.org/bots/api#inlinequeryresultaudio); -   [InlineQueryResultContact](https://core.telegram.org/bots/api#inlinequeryresultcontact); -   [InlineQueryResultGame](https://core.telegram.org/bots/api#inlinequeryresultgame); -   [InlineQueryResultDocument](https://core.telegram.org/bots/api#inlinequeryresultdocument); -   [InlineQueryResultGif](https://core.telegram.org/bots/api#inlinequeryresultgif); -   [InlineQueryResultLocation](https://core.telegram.org/bots/api#inlinequeryresultlocation); -   [InlineQueryResultMpeg4Gif](https://core.telegram.org/bots/api#inlinequeryresultmpeg4gif); -   [InlineQueryResultPhoto](https://core.telegram.org/bots/api#inlinequeryresultphoto); -   [InlineQueryResultVenue](https://core.telegram.org/bots/api#inlinequeryresultvenue); -   [InlineQueryResultVideo](https://core.telegram.org/bots/api#inlinequeryresultvideo); -   [InlineQueryResultVoice](https://core.telegram.org/bots/api#inlinequeryresultvoice); ; **Note:** All URLs passed in inline query results will be available to end users and therefore must be assumed to be **public**.')]
#[See('https://core.telegram.org/bots/api#inlinequeryresult')]
class InlineQueryResultTypeDTO implements TgApiTypeDTOContract
{
    public readonly TgApiTypesEnum $dto;

    public readonly TgApiEntityScopeEnum $entityScope;

    public function __construct()
    {
        $this->dto = static::tgApiEntity();
        $this->entityScope = static::tgEntityScope();
    }

    public static function tgApiEntity(): TgApiTypesEnum
    {
        return TgApiTypesEnum::InlineQueryResult;
    }

    public static function tgEntityScope(): TgApiEntityScopeEnum
    {
        return TgApiEntityScopeEnum::Type;
    }

    public static function tgPropertyMetas(): array
    {
        return [];
    }
}
