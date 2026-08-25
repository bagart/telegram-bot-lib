<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Transports;

use BAGArt\ASKClient\Client\ASKFuture;
use BAGArt\ASKClient\Client\CallbackProducer;
use BAGArt\ASKClient\Contracts\Pipeline\ASKFutureContract;
use BAGArt\ASKClient\Contracts\Transport\HttpTransportContract;
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
        private readonly TgRequestFactory $requestFactory = new TgRequestFactory,
        private readonly TgResponseDecoder $decoder = new TgResponseDecoder,
        private readonly ?ASKPromiseResolverContract $promiseResolver = null,
    ) {}

    public function request(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
        array $files = [],
    ): array {
        $httpRequest = $this->requestFactory->make(
            tgMethodName: $method,
            parameters: $params,
            botConfig: $config,
            timeout: $timeout,
            files: $files,
        );

        return $this->decoder->decode(
            (string) $this->httpTransport->request($httpRequest)->getBody(),
        );
    }

    public function requestAsync(
        TgBotConfig $config,
        string $method,
        array $params = [],
        ?int $timeout = null,
        array $files = [],
    ): ASKFutureContract {
        $httpRequest = $this->requestFactory->make(
            tgMethodName: $method,
            parameters: $params,
            botConfig: $config,
            timeout: $timeout,
            files: $files,
        );

        $promise = $this->httpTransport->requestAsync($httpRequest);

        return ASKFuture::pending(
            new CallbackProducer(
                function () use ($promise): array {
                    return $this->decoder->decode(
                        (string) $promise->await()->getBody(),
                    );
                }
            ),
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
