<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Reason for the refund. Currently, one of “post\_deleted” if the post was deleted within 24 hours of being posted or removed from scheduled messages without being posted, or “payment\_refunded” if the payer refunded their payment.')]
#[See('https://core.telegram.org/bots/api#suggestedpostrefunded')]
enum ReasonEnum: string implements TgApiEnumContract
{
    case POST_DELETED = 'post_deleted';
    case PAYMENT_REFUNDED = 'payment_refunded';
}
