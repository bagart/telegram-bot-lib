<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Codec that was used to encode the video, for example, “h264”, “h265”, or “av01”')]
#[See('https://core.telegram.org/bots/api#videoquality')]
enum CodecEnum: string implements TgApiEnumContract
{
    case H264 = 'h264';
    case H265 = 'h265';
    case AV01 = 'av01';
}
