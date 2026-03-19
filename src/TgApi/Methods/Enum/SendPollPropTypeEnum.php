<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Poll type, “quiz” or “regular”, defaults to “regular”')]
#[See('https://core.telegram.org/bots/api#sendpoll')]
enum SendPollPropTypeEnum: string implements TgApiEnumContract
{
    case QUIZ = 'quiz';
    case REGULAR = 'regular';
}
