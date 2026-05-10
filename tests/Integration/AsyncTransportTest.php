<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Integration;

use BAGArt\TelegramBot\Tests\Unit\ApiCommunication\Transport\MockTgTransport;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for async transport operations with Fibers.
 */
class AsyncTransportTest extends TestCase
{
    private MockTgTransport $mockTransport;

    public function testSyncRequestWithMockTransport(): void
    {
        $this->mockTransport->setResponse('getMe', [
            'ok' => true,
            'result' => [
                'id' => 123456789,
                'is_bot' => true,
                'first_name' => 'TestBot',
                'username' => 'testbot'
            ]
        ]);

        $response = $this->mockTransport->request('getMe', [], 'test_token');

        $this->assertTrue($response['ok']);
        $this->assertEquals(123456789, $response['result']['id']);
        $this->assertEquals('testbot', $response['result']['username']);
    }

    public function testAsyncRequestWithMockTransport(): void
    {
        $this->mockTransport->setResponse('sendMessage', [
            'ok' => true,
            'result' => [
                'message_id' => 42,
                'chat' => ['id' => 123, 'type' => 'private'],
                'text' => 'Hello'
            ]
        ]);

        $promise = $this->mockTransport->requestAsync('sendMessage', [
            'chat_id' => 123,
            'text' => 'Hello'
        ], 'test_token');

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $response = $promise->wait();

        $this->assertTrue($response['ok']);
        $this->assertEquals(42, $response['result']['message_id']);
    }

    public function testMultipleAsyncRequests(): void
    {
        $this->mockTransport->setResponse('getMe1', [
            'ok' => true,
            'result' => ['id' => 1]
        ]);
        $this->mockTransport->setResponse('getMe2', [
            'ok' => true,
            'result' => ['id' => 2]
        ]);

        $promise1 = $this->mockTransport->requestAsync('getMe1', [], 'token1');
        $promise2 = $this->mockTransport->requestAsync('getMe2', [], 'token2');

        $response1 = $promise1->wait();
        $response2 = $promise2->wait();

        $this->assertEquals(1, $response1['result']['id']);
        $this->assertEquals(2, $response2['result']['id']);
    }

    public function testPromiseChaining(): void
    {
        $this->mockTransport->setResponse('getMe', [
            'ok' => true,
            'result' => ['id' => 123]
        ]);

        $promise = $this->mockTransport->requestAsync('getMe', [], 'token');

        $chainedPromise = $promise->then(function (array $response) {
            return $response['result']['id'];
        });

        $result = $chainedPromise->wait();

        $this->assertEquals(123, $result);
    }

    public function testPromiseCatch(): void
    {
        $this->mockTransport->setError('getMe', new \RuntimeException('Unauthorized'));

        $promise = $this->mockTransport->requestAsync('getMe', [], 'invalid_token');

        $caught = false;
        $promise->otherwise(function ($e) use (&$caught) {
            $caught = true;
        });

        try {
            $promise->wait();
            $this->fail('Expected exception was not thrown');
        } catch (\Throwable $e) {
            Utils::queue()->run();
            $this->assertTrue($caught);
            $this->assertEquals('Unauthorized', $e->getMessage());
        }
    }

    public function testConcurrentRequestsProcessedInOrder(): void
    {
        $responses = [];

        $this->mockTransport->setResponse('sendMessage', [
            'ok' => true,
            'result' => ['message_id' => 1]
        ]);

        $p1 = $this->mockTransport->requestAsync('sendMessage', ['chat_id' => 1, 'text' => 'a'], 't');
        $p2 = $this->mockTransport->requestAsync('sendMessage', ['chat_id' => 2, 'text' => 'b'], 't');

        $responses[] = $p1->wait();
        $responses[] = $p2->wait();

        $this->assertTrue($responses[0]['ok']);
        $this->assertTrue($responses[1]['ok']);
    }

    protected function setUp(): void
    {
        $this->mockTransport = new MockTgTransport();
    }
}
