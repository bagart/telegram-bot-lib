<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests;

use BAGArt\TelegramBot\ApiCommunication\TgBotApiClient;
use BAGArt\TelegramBot\Tests\Unit\ApiCommunication\Transport\MockTgTransport;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRetryPolicyContract;
use BAGArt\TelegramBot\Exceptions\TgApi\TgFloodWaitException;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for the ApiClient with MockTransport.
 */
class TgBotApiClientTest extends TestCase
{
    private MockTgTransport $transport;
    private TgBotApiClient $client;

    public function testSuccessfulRequest(): void
    {
        $this->transport->setResponse('sendMessage', [
            'ok' => true,
            'result' => ['message_id' => 123]
        ]);

        $response = $this->client->requestAsync('bot_token', 'sendMessage', ['chat_id' => 1, 'text' => 'hi'])->wait();

        $this->assertTrue($response['ok']);
        $this->assertEquals(123, $response['result']['message_id']);
    }

    public function testFloodWaitException(): void
    {
        $this->transport->setResponse('sendMessage', [
            'ok' => false,
            'error_code' => 429,
            'description' => 'Too many requests: retry after 30'
        ]);

        $this->expectException(TgFloodWaitException::class);
        $this->expectExceptionMessage('Too many requests: retry after 30');

        $this->client->requestAsync('bot_token', 'sendMessage', ['chat_id' => 1, 'text' => 'hi'])->wait();
    }

    public function testBadRequestException(): void
    {
        $this->transport->setResponse('sendMessage', [
            'ok' => false,
            'error_code' => 400,
            'description' => 'Bad Request: chat not found'
        ]);

        $this->expectException(\BAGArt\TelegramBot\Exceptions\TgApi\TgBadRequestException::class);
        $this->expectExceptionMessage('Bad Request: chat not found');

        $this->client->requestAsync('bot_token', 'sendMessage', ['chat_id' => 999, 'text' => 'hi'])->wait();
    }

    protected function setUp(): void
    {
        $this->transport = new MockTgTransport();

        // Mocking dependencies
        $rateLimiter = $this->createMock(TgRateLimiterContract::class);
        $rateLimiter->method('acquire')->willReturn(true);

        $circuitBreaker = $this->createMock(TgCircuitBreakerContract::class);
        $circuitBreaker->method('canExecute')->willReturn(true);

        $retryPolicy = $this->createMock(TgRetryPolicyContract::class);

        $this->client = new TgBotApiClient(
            $rateLimiter,
            $circuitBreaker,
            $retryPolicy,
            $this->transport
        );
    }
}
