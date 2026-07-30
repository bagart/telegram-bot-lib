<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\ApiCommunication\Transport;

use BAGArt\ASKClient\Contracts\Transport\HttpTransportContract;
use BAGArt\ASKClient\Dto\ASKHttpRequest;
use BAGArt\ASKClient\Dto\ASKHttpResponse;
use BAGArt\AsyncKernel\Contracts\ASKPromiseContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Promise\ASKPromise;

/**
 * Test double for {@see HttpTransportContract}.
 *
 * Stores canned array responses keyed by request name and serves them wrapped into
 * {@see ASKHttpResponse} objects — mirroring what the real HTTP transport produces from the
 * network. The Telegram response decoder (now in TgBotApiTransport) turns the JSON body
 * back into the array callers assert against.
 */
class MockTgTransport implements HttpTransportContract
{
    /** @var array<string, array> */
    private array $responses = [];

    /** @var array<string, \Throwable> */
    private array $errors = [];

    public function setResponse(string $method, array $response): void
    {
        $this->responses[$method] = $response;
    }

    public function setError(string $method, \Throwable $error): void
    {
        $this->errors[$method] = $error;
    }

    public function request(ASKHttpRequest $request): ASKHttpResponse
    {
        return $this->requestAsync($request)->await();
    }

    public function requestAsync(ASKHttpRequest $request): ASKPromiseContract
    {
        $methodName = $request->requestName;

        if (isset($this->errors[$methodName])) {
            return ASKPromise::rejected($this->errors[$methodName]);
        }

        $response = $this->responses[$methodName] ?? ['ok' => false, 'description' => 'Method not mocked'];

        return ASKPromise::resolved(ASKHttpResponse::fromJson($response));
    }

    /**
     * No tickables — the mock resolves promises synchronously and has no network I/O to drive.
     *
     * @return ASKTickableContract[]
     */
    public function tickable(): array
    {
        return [];
    }
}
