<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Poll type, currently can be “regular” or “quiz”')]
#[See('https://core.telegram.org/bots/api#poll')]
enum PollPropTypeEnum: string implements TgApiEnumContract
{
    case REGULAR = 'regular';
    case QUIZ = 'quiz';
}
