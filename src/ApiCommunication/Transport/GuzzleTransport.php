<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Transport;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Exceptions\TgTransportException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleTransport implements TgBotApiTransportContract
{
    private readonly ?ClientInterface $client;

    public function __construct(
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? new \GuzzleHttp\Client();
    }

    public function request(string $tgMethodName, array $parameters, string $token): array
    {
        $url = "https://api.telegram.org/bot{$token}/{$tgMethodName}";

        try {
            $response = $this->client->request('POST', $url, [
                'form_params' => $parameters,
            ]);
        } catch (\Throwable $e) {
            throw new TgTransportException(
                sprintf('Guzzle request failed for method=%s: %s', $tgMethodName, $e->getMessage()),
                previous: $e
            );
        }

        return $this->parseResponse($response);
    }

    private function parseResponse(ResponseInterface $response): array
    {
        try {
            return json_decode(
                (string)$response->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            throw new TgTransportException(
                sprintf('Invalid Telegram JSON response: %s', $e->getMessage()),
                previous: $e
            );
        }
    }

    public function requestAsync(string $tgMethodName, array $parameters, string $token): PromiseInterface
    {
        // Guzzle transport is synchronous — wrap the blocking call in an already-resolved promise.
        // This keeps the interface consistent with TgCurlMultiTransport where request() calls
        // requestAsync() → while(PENDING) tick() → wait(). Since Guzzle has no tick(), the promise
        // must be resolved immediately so the while loop exits on the first check.
        try {
            return \GuzzleHttp\Promise\Create::promiseFor($this->request($tgMethodName, $parameters, $token));
        } catch (\Throwable $e) {
            return \GuzzleHttp\Promise\Create::rejectionFor($e);
        }
    }

    public function tick(): void
    {
    }

    public function hasActiveHandles(): bool
    {
        return false;
    }
}
