<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Mode for parsing entities in the explanation. See [formatting options](https://core.telegram.org/bots/api#formatting-options) for more details.')]
#[See('https://core.telegram.org/bots/api#sendpoll')]
enum ExplanationParseModeEnum: string implements TgApiEnumContract
{
    case HTML = 'HTML';
    case MARKDOWNV2 = 'MarkdownV2';
    case MARKDOWN = 'Markdown';
}
