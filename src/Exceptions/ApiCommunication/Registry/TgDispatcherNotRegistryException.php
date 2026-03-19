<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry;

use BAGArt\TelegramBot\Exceptions\TgTechnicalException;

class TgDispatcherNotRegistryException extends TgTechnicalException
{
    public function __construct(
        public string $type,
    ) {
        parent::__construct("Dispatcher Not Registry: $type");
    }
}
