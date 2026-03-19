<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of the chat, can be either “private”, “group”, “supergroup” or “channel”')]
#[See('https://core.telegram.org/bots/api#chat')]
enum ChatPropTypeEnum: string implements TgApiEnumContract
{
    case PRIVATE = 'private';
    case GROUP = 'group';
    case SUPERGROUP = 'supergroup';
    case CHANNEL = 'channel';
}
