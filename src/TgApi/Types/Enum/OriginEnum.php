<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Origin of the gift. Currently, either “upgrade” for gifts upgraded from regular gifts, “transfer” for gifts transferred from other users or channels, “resale” for gifts bought from other users, “gifted\_upgrade” for upgrades purchased after the gift was sent, or “offer” for gifts bought or sold through gift purchase offers')]
#[See('https://core.telegram.org/bots/api#uniquegiftinfo')]
enum OriginEnum: string implements TgApiEnumContract
{
    case UPGRADE = 'upgrade';
    case TRANSFER = 'transfer';
    case RESALE = 'resale';
    case GIFTED_UPGRADE = 'gifted_upgrade';
    case OFFER = 'offer';
}
