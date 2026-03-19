<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;

interface TgBotApiTransportContract
{
    public const int DEFAULT_TIMEOUT_SECONDS = 10;

    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
    ): array;

    public function requestAsync(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
    ): ASKFutureContract;
}
