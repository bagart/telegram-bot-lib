<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\ApiCommunication\Transport;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

class MockTgTransport implements TgBotApiTransportContract
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

    public function request(string $tgMethodName, array $parameters, string $token): array
    {
        return $this->requestAsync($tgMethodName, $parameters, $token)->wait();
    }

    public function requestAsync(string $tgMethodName, array $parameters, string $token): PromiseInterface
    {
        if (isset($this->errors[$tgMethodName])) {
            $promise = new Promise(fn () => null);
            $promise->reject($this->errors[$tgMethodName]);
            return $promise;
        }

        $response = $this->responses[$tgMethodName] ?? ['ok' => false, 'description' => 'Method not mocked'];

        $promise = new Promise(fn () => null);
        $promise->resolve($response);

        return $promise;
    }

    public function tick(): void
    {
    }

    public function hasActiveHandles(): bool
    {
        return false;
    }
}
