<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('If _quiz_ is passed, the user will be allowed to create only polls in the quiz mode. If _regular_ is passed, only regular polls will be allowed. Otherwise, the user will be allowed to create a poll of any type.')]
#[See('https://core.telegram.org/bots/api#keyboardbuttonpolltype')]
enum KeyboardButtonPollTypePropTypeEnum: string implements TgApiEnumContract
{
    case QUIZ = 'quiz';
    case REGULAR = 'regular';
}
