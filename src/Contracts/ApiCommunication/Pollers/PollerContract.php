<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\TgUpdateConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotOutputWrapper;

interface PollerContract
{
    public const string TYPE = 'undefined';

    public function run(
        TgUpdateConfig $config,
    ): void;

    public static function build(
        TgTypeDTOProcessorContract $updateProcessor,
        ?TgBotApiDTOClientContract $dtoClient = null,
        ?TgBotLogWrapper $logger = null,
        ?TgBotOutputWrapper $output = null,
    ): self;
}
