<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Mode for parsing entities in the text. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details. Entities other than “bold”, “italic”, “underline”, “strikethrough”, “spoiler”, and “custom\_emoji” are ignored.')]
#[See('https://core.telegram.org/bots/api#giftpremiumsubscription')]
enum TextParseModeEnum: string implements TgApiEnumContract
{
    case HTML = 'HTML';
    case MARKDOWNV2 = 'MarkdownV2';
    case MARKDOWN = 'Markdown';
}
