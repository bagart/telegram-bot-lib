<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of the sticker, currently one of “regular”, “mask”, “custom\_emoji”. The type of the sticker is independent from its format, which is determined by the fields _is\_animated_ and _is\_video_.')]
#[See('https://core.telegram.org/bots/api#sticker')]
enum StickerPropTypeEnum: string implements TgApiEnumContract
{
    case REGULAR = 'regular';
    case MASK = 'mask';
    case CUSTOM_EMOJI = 'custom_emoji';
}
