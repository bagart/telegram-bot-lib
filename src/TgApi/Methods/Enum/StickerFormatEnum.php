<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Format of the sticker, must be one of “static”, “animated”, “video”')]
#[See('https://core.telegram.org/bots/api#uploadstickerfile')]
enum StickerFormatEnum: string implements TgApiEnumContract
{
    case STATIC = 'static';
    case ANIMATED = 'animated';
    case VIDEO = 'video';
}
