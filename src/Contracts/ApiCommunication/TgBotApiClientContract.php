<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiEntityEnumContract;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use GuzzleHttp\Promise\PromiseInterface;

interface TgBotApiClientContract
{
    public static function build(
        TgBotCacheWrapper $cache,
        ?TgBotApiTransportContract $transport = null,
        ?TgBotLogWrapper $logger = null,
    ): self;

    public function request(
        string $token,
        TgApiEntityEnumContract|string $method,
        array $params = [],
    ): array;

    public function requestAsync(
        string $token,
        TgApiEntityEnumContract|string $method,
        array $params = [],
        int $attempt = 1,
    ): PromiseInterface;

    public function tick(): void;
}
