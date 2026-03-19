<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Clients;

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\ASKClient\Contracts\Transporting\HttpTransportContract;
use BAGArt\TelegramBot\ApiCommunication\Transports\TgBotApiTransport;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;

final class TgBotApiClient implements TgBotApiClientContract
{
    public function __construct(
        private readonly TgBotApiTransportContract $transport,
    ) {
    }

    public static function build(HttpTransportContract $httpTransport): self
    {
        return new self(
            new TgBotApiTransport($httpTransport),
        );
    }

    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
    ): array {
        return $this->transport->request($config, $method, $params);
    }

    public function requestAsync(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
    ): ASKFutureContract {
        return $this->transport->requestAsync($config, $method, $params, $timeout);
    }
}
