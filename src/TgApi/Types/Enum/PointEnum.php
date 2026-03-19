<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('The part of the face relative to which the mask should be placed. One of “forehead”, “eyes”, “mouth”, or “chin”.')]
#[See('https://core.telegram.org/bots/api#maskposition')]
enum PointEnum: string implements TgApiEnumContract
{
    case FOREHEAD = 'forehead';
    case EYES = 'eyes';
    case MOUTH = 'mouth';
    case CHIN = 'chin';
}
