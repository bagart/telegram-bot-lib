<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use GuzzleHttp\Promise\PromiseInterface;

interface TgBotApiClientContract
{
    public function requestAsync(
        string $token,
        TgApiEntityEnumContract|string $method,
        array $params,
        int $attempt = 1,
    ): PromiseInterface;
}
