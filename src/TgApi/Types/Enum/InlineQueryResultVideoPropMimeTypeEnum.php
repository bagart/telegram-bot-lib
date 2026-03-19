<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('MIME type of the content of the video URL, “text/html” or “video/mp4”')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultvideo')]
enum InlineQueryResultVideoPropMimeTypeEnum: string implements TgApiEnumContract
{
    case TEXT_HTML = 'text/html';
    case VIDEO_MP4 = 'video/mp4';
}
