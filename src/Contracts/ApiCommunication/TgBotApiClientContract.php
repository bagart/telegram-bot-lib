<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;

interface TgBotApiClientContract
{
    /**
     * @return array{ok: bool, result?: mixed, error_code?: int, description?: string}
     */
    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
    ): array;

    public function requestAsync(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
    ): ASKFutureContract;
}
