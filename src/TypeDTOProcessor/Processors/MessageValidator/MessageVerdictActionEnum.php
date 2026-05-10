<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageValidator;

enum MessageVerdictActionEnum: string
{
    case Restrict = 'restrict';
    case Ban = 'ban';
}
