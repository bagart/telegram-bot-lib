<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Types\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('MIME type of the content of the file, either “application/pdf” or “application/zip”')]
#[See('https://core.telegram.org/bots/api#inlinequeryresultdocument')]
enum InlineQueryResultDocumentPropMimeTypeEnum: string implements TgApiEnumContract
{
    case APPLICATION_PDF = 'application/pdf';
    case APPLICATION_ZIP = 'application/zip';
}
