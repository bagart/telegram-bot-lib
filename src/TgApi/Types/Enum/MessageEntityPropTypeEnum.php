<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of the entity. Currently, can be “mention” (`@username`), “hashtag” (`#hashtag` or `#hashtag@chatusername`), “cashtag” (`$USD` or `$USD@chatusername`), “bot\_command” (`/start@jobs_bot`), “url” (`https://telegram.org`), “email” (`do-not-reply@telegram.org`), “phone\_number” (`+1-212-555-0123`), “bold” (**bold text**), “italic” (_italic text_), “underline” (underlined text), “strikethrough” (strikethrough text), “spoiler” (spoiler message), “blockquote” (block quotation), “expandable\_blockquote” (collapsed-by-default block quotation), “code” (monowidth string), “pre” (monowidth block), “text\_link” (for clickable text URLs), “text\_mention” (for users [without usernames](https://telegram.org/blog/edit#new-mentions)), “custom\_emoji” (for inline custom emoji stickers), or “date\_time” (for formatted date and time)')]
#[See('https://core.telegram.org/bots/api#messageentity')]
enum MessageEntityPropTypeEnum: string implements TgApiEnumContract
{
    case MENTION = 'mention';
    case HASHTAG = 'hashtag';
    case CASHTAG = 'cashtag';
    case BOT_COMMAND = 'bot_command';
    case URL = 'url';
    case EMAIL = 'email';
    case PHONE_NUMBER = 'phone_number';
    case BOLD = 'bold';
    case ITALIC = 'italic';
    case UNDERLINE = 'underline';
    case STRIKETHROUGH = 'strikethrough';
    case SPOILER = 'spoiler';
    case BLOCKQUOTE = 'blockquote';
    case EXPANDABLE_BLOCKQUOTE = 'expandable_blockquote';
    case CODE = 'code';
    case PRE = 'pre';
    case TEXT_LINK = 'text_link';
    case TEXT_MENTION = 'text_mention';
    case CUSTOM_EMOJI = 'custom_emoji';
    case DATE_TIME = 'date_time';
}
