<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Rarity of the model if it is a crafted model. Currently, can be “uncommon”, “rare”, “epic”, or “legendary”.')]
#[See('https://core.telegram.org/bots/api#uniquegiftmodel')]
enum RarityEnum: string implements TgApiEnumContract
{
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';
}
