<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests;

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\TelegramBot\ApiCommunication\Clients\TgBotApiClient;
use BAGArt\TelegramBot\ApiCommunication\Transports\TgBotApiTransport;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Tests\Unit\ApiCommunication\Transport\MockTgTransport;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for TgBotApiClient via TgBotApiTransport → MockTgTransport.
 */
class TgBotApiClientTest extends TestCase
{
    private MockTgTransport $transport;
    private TgBotApiClient $client;

    public function testSuccessfulRequest(): void
    {
        $this->transport->setResponse('sendMessage', [
            'ok' => true,
            'result' => ['message_id' => 123],
        ]);

        $response = $this->client->requestAsync(
            new TgBotConfig(token: '', botId: 'test'),
            'sendMessage',
            ['chat_id' => 1, 'text' => 'hi'],
        )->await();

        $this->assertTrue($response['ok']);
        $this->assertEquals(123, $response['result']['message_id']);
    }

    public function testRequestAsyncReturnsAskFuture(): void
    {
        $this->transport->setResponse('getMe', [
            'ok' => true,
            'result' => ['id' => 1, 'username' => 'testbot'],
        ]);

        $future = $this->client->requestAsync(
            new TgBotConfig(token: '', botId: 'test'),
            'getMe',
        );

        $this->assertInstanceOf(ASKFutureContract::class, $future);
    }

    public function testSynchronousRequest(): void
    {
        $this->transport->setResponse('getMe', [
            'ok' => true,
            'result' => ['id' => 1, 'username' => 'testbot'],
        ]);

        $response = $this->client->request(
            new TgBotConfig(token: '', botId: 'test'),
            'getMe',
        );

        $this->assertTrue($response['ok']);
        $this->assertEquals('testbot', $response['result']['username']);
    }

    public function testBuildCreatesWorkingClient(): void
    {
        $this->transport->setResponse('getMe', [
            'ok' => true,
            'result' => ['id' => 1, 'username' => 'builtbot'],
        ]);

        $client = TgBotApiClient::build($this->transport);

        $response = $client->request(
            new TgBotConfig(token: '', botId: 'test'),
            'getMe',
        );

        $this->assertTrue($response['ok']);
        $this->assertEquals('builtbot', $response['result']['username']);
    }

    protected function setUp(): void
    {
        $this->transport = new MockTgTransport();
        $this->client = new TgBotApiClient(
            new TgBotApiTransport($this->transport),
        );
    }
}
