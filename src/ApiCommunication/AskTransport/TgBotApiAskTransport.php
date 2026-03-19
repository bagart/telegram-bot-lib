<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

use BAGArt\ASKClient\ASKFuture;
use BAGArt\ASKClient\Contracts\ASKContextContract;
use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\ASKClient\Contracts\Client\ApiClientContract;
use BAGArt\ASKClient\Contracts\Transporting\ASKTransportContract;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRequestFactory;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgResponseDecoder;

/**
 * ASKClient transport that bridges TgApiAskOperation → {@see ApiClientContract}.
 *
 * Sits on top of the ASKClient ApiClient, which already provides rate limiting,
 * promise resolution and tickable driving of the underlying network client. This
 * adapter owns only the Telegram-specific concern: turning the raw JSON response
 * body into the decoded array shape callers expect.
 *
 * Flow: ASKClient::execute(TgApiAskOperation) → TgBotApiAskTransport::execute()
 *       → TgRequestFactory::make() → ApiClientContract::requestAsync()
 *       → TgResponseDecoder::decode() → ASKFuture
 */
final class TgBotApiAskTransport implements ASKTransportContract
{
    public function __construct(
        private readonly ApiClientContract $apiClient,
        private readonly TgRequestFactory $requestFactory = new TgRequestFactory(),
        private readonly TgResponseDecoder $decoder = new TgResponseDecoder(),
    ) {
    }

    public function execute(object $operation, ASKContextContract $context): ASKFutureContract
    {
        if (!$operation instanceof TgApiAskOperation) {
            return ASKFuture::failed(
                new \InvalidArgumentException(
                    sprintf('Expected TgApiAskOperation, got %s', get_debug_type($operation)),
                )
            );
        }

        $httpRequest = $this->requestFactory->make(
            tgMethodName: $operation->method,
            parameters: $operation->params,
            botConfig: $operation->config,
            timeout: $operation->timeout,
        );

        $promise = $this->apiClient
            ->requestAsync($httpRequest)
            ->then(
                fn ($response): array => $this
                    ->decoder->decode((string)$response->getBody()),
            );

        return ASKFuture::pending(fn (): mixed => $promise->await());
    }
}
