<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of the transaction, currently one of “invoice\_payment” for payments via invoices, “paid\_media\_payment” for payments for paid media, “gift\_purchase” for gifts sent by the bot, “premium\_purchase” for Telegram Premium subscriptions gifted by the bot, “business\_account\_transfer” for direct transfers from managed business accounts')]
#[See('https://core.telegram.org/bots/api#transactionpartneruser')]
enum TransactionTypeEnum: string implements TgApiEnumContract
{
    case INVOICE_PAYMENT = 'invoice_payment';
    case PAID_MEDIA_PAYMENT = 'paid_media_payment';
    case GIFT_PURCHASE = 'gift_purchase';
    case PREMIUM_PURCHASE = 'premium_purchase';
    case BUSINESS_ACCOUNT_TRANSFER = 'business_account_transfer';
}
