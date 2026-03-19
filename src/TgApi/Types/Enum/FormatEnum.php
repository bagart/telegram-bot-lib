<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Format of the added sticker, must be one of “static” for a **.WEBP** or **.PNG** image, “animated” for a **.TGS** animation, “video” for a **.WEBM** video')]
#[See('https://core.telegram.org/bots/api#inputsticker')]
enum FormatEnum: string implements TgApiEnumContract
{
    case STATIC = 'static';
    case ANIMATED = 'animated';
    case VIDEO = 'video';
}
