<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

enum MessageVerdictActionEnum: string
{
    case Restrict = 'restrict';
    case Ban = 'ban';
}
