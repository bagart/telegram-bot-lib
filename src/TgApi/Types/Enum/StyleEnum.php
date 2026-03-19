<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Style of the button. Must be one of “danger” (red), “success” (green) or “primary” (blue). If omitted, then an app-specific style is used.')]
#[See('https://core.telegram.org/bots/api#inlinekeyboardbutton')]
enum StyleEnum: string implements TgApiEnumContract
{
    case DANGER = 'danger';
    case SUCCESS = 'success';
    case PRIMARY = 'primary';
}
