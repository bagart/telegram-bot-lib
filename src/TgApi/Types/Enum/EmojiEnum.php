<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Emoji on which the dice throw animation is based')]
#[See('https://core.telegram.org/bots/api#dice')]
enum EmojiEnum: string implements TgApiEnumContract
{
    case X27f09f8eb227 = '🎲';
    case X27f09f8eaf27 = '🎯';
    case X27f09f8eb327 = '🎳';
    case X27f09f8f8027 = '🏀';
    case X27e29abd27 = '⚽';
    case X27f09f8eb027 = '🎰';
}
