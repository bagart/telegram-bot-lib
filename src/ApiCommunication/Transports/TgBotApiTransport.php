<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Transports;

use BAGArt\ASKClient\ASKFuture;
use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\ASKClient\Contracts\Transporting\HttpTransportContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\AsyncKernel\Contracts\Promise\ASKPromiseResolverContract;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRequestFactory;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgResponseDecoder;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;

final class TgBotApiTransport implements TgBotApiTransportContract, WithASKTickableContract
{
    public function __construct(
        private readonly HttpTransportContract $httpTransport,
        private readonly TgRequestFactory $requestFactory = new TgRequestFactory(),
        private readonly TgResponseDecoder $decoder = new TgResponseDecoder(),
        private readonly ?ASKPromiseResolverContract $promiseResolver = null,
    ) {
    }

    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
    ): array {
        $httpRequest = $this->requestFactory->make(
            tgMethodName: $method,
            parameters: $params,
            botConfig: $config,
            timeout: $timeout,
        );

        return $this->decoder->decode(
            (string)$this->httpTransport->request($httpRequest)->getBody(),
        );
    }

    public function requestAsync(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
    ): ASKFutureContract {
        $httpRequest = $this->requestFactory->make(
            tgMethodName: $method,
            parameters: $params,
            botConfig: $config,
            timeout: $timeout,
        );

        $promise = $this->httpTransport->requestAsync($httpRequest);

        return ASKFuture::pending(
            function () use ($promise): array {
                return $this->decoder->decode(
                    (string)$promise->await()->getBody(),
                );
            },
        );
    }

    public function tickable(): array
    {
        if ($this->httpTransport instanceof ASKTickableContract) {
            return [$this->httpTransport];
        }

        return [];
    }
}
