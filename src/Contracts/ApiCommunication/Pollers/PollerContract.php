<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers;

use BAGArt\TelegramBot\TypeDTOProcessor\DtoProcessorConfig;

interface PollerContract
{
    public const string TYPE = 'undefined';

    public function run(
        DtoProcessorConfig $config,
    ): void;
}
