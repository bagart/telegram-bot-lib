<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TgApi\Methods\Enum;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEnumContract;

#[Warning('File is auto-generated. Use DtoGenerator to change')]
#[Description('Type of action to broadcast. Choose one, depending on what the user is about to receive: _typing_ for [text messages](https://core.telegram.org/bots/api#sendmessage), _upload\_photo_ for [photos](https://core.telegram.org/bots/api#sendphoto), _record\_video_ or _upload\_video_ for [videos](https://core.telegram.org/bots/api#sendvideo), _record\_voice_ or _upload\_voice_ for [voice notes](https://core.telegram.org/bots/api#sendvoice), _upload\_document_ for [general files](https://core.telegram.org/bots/api#senddocument), _choose\_sticker_ for [stickers](https://core.telegram.org/bots/api#sendsticker), _find\_location_ for [location data](https://core.telegram.org/bots/api#sendlocation), _record\_video\_note_ or _upload\_video\_note_ for [video notes](https://core.telegram.org/bots/api#sendvideonote).')]
#[See('https://core.telegram.org/bots/api#sendchataction')]
enum ActionEnum: string implements TgApiEnumContract
{
    case TYPING = 'typing';
    case UPLOAD_PHOTO = 'upload_photo';
    case RECORD_VIDEO = 'record_video';
    case UPLOAD_VIDEO = 'upload_video';
    case RECORD_VOICE = 'record_voice';
    case UPLOAD_VOICE = 'upload_voice';
    case UPLOAD_DOCUMENT = 'upload_document';
    case CHOOSE_STICKER = 'choose_sticker';
    case FIND_LOCATION = 'find_location';
    case RECORD_VIDEO_NOTE = 'record_video_note';
    case UPLOAD_VIDEO_NOTE = 'upload_video_note';
}
