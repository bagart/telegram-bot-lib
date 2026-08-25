<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\ASKClient\Contracts\Pipeline\ASKFutureContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;

interface TgBotApiClientContract
{
    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, \CURLFile|resource|string>  $files
     * @return array{ok: bool, result?: mixed, error_code?: int, description?: string}
     */
    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
        array $files = [],
    ): array;

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, \CURLFile|resource|string>  $files
     */
    public function requestAsync(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
        array $files = [],
    ): ASKFutureContract;
}
