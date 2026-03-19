<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Period after which the story is moved to the archive, in seconds; must be one of `6 * 3600`, `12 * 3600`, `86400`, or `2 * 86400`')]
#[See('https://core.telegram.org/bots/api#repoststory')]
enum ActivePeriodEnum: int implements TgApiEnumContract
{
    case PERIOD_6H = 21600;
    case PERIOD_12H = 43200;
    case PERIOD_1D = 86400;
    case PERIOD_2D = 172800;
}
