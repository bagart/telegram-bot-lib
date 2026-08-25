<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\ASKClient\Contracts\Pipeline\ASKFutureContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;

interface TgBotApiTransportContract
{
    public const int DEFAULT_TIMEOUT_SECONDS = 10;

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, \CURLFile|resource|string>  $files
     */
    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
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
