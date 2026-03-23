<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry;

use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiCommunicationException;

class TgPollerNotRegistryException extends TgApiCommunicationException
{
    public function __construct(
        public string $type,
    ) {
    }
}
