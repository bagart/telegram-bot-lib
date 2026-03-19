<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('MIME type of the thumbnail, must be one of “image/jpeg”, “image/gif”, or “video/mp4”. Defaults to “image/jpeg”')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultmpeg4gif')]
enum ThumbnailMimeTypeEnum: string implements TgApiEnumContract
{
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_GIF = 'image/gif';
    case VIDEO_MP4 = 'video/mp4';
}
