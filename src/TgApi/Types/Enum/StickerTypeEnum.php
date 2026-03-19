<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of stickers in the set, currently one of “regular”, “mask”, “custom\_emoji”')]
#[See('https://core.telegram.org/bots/api#stickerset')]
enum StickerTypeEnum: string implements TgApiEnumContract
{
    case REGULAR = 'regular';
    case MASK = 'mask';
    case CUSTOM_EMOJI = 'custom_emoji';
}
