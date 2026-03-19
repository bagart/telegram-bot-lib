<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of stickers in the set, pass “regular”, “mask”, or “custom\_emoji”. By default, a regular sticker set is created.')]
#[See('https://core.telegram.org/bots/api#createnewstickerset')]
enum StickerTypeEnum: string implements TgApiEnumContract
{
    case REGULAR = 'regular';
    case MASK = 'mask';
    case CUSTOM_EMOJI = 'custom_emoji';
}
