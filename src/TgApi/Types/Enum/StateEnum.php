<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('State of the suggested post. Currently, it can be one of “pending”, “approved”, “declined”.')]
#[See('https://core.telegram.org/bots/api#suggestedpostinfo')]
enum StateEnum: string implements TgApiEnumContract
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
}
