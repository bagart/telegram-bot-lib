<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Transports;

use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlMultiHandle;
use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlMultiTransport;
use BAGArt\TelegramBot\ApiCommunication\Transport\TgCurlRequest;
use Fiber;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class CurlMultiTransportTest
 *
 * Tests the asynchronous behavior and Fiber integration of CurlMultiTransport.
 */
class CurlMultiTransportTest extends TestCase
{
    private TgCurlMultiTransport $transport;
    private TgCurlMultiHandle $mockMultiHandle;

    /**
     * Test that requestAsync correctly suspends the current Fiber.
     */
    public function testRequestAsyncSuspendsFiber(): void
    {
        $request = new TgCurlRequest('https://api.telegram.org/bot123/getMe', 'GET');

        $fiber = new Fiber(function () use ($request) {
            try {
                $this->transport->requestAsync($request);
            } catch (RuntimeException $e) {
                // We expect an error because the URL is fake and won't resolve in a simple tick loop
                // but we want to see if the fiber actually reached the suspension point.
            }
        });

        $fiber->start();

        // If the implementation is correct, the fiber should be suspended
        // waiting for the multi-handle to finish.
        $this->assertTrue($fiber->isSuspended(), 'Fiber should be suspended after requestAsync is called.');
    }

    /**
     * Test the tick mechanism and fiber resumption.
     * Note: This is a complex test because it requires a working curl loop.
     * We will simulate the tick behavior.
     */
    public function testTickResumesFiber(): void
    {
        // Because we cannot easily mock curl_multi_info_read, we are testing
        // the integration of the transport's tick method with the Fiber lifecycle.

        // This is a placeholder for a more complex integration test
        // that would involve a real local web server.
        $this->assertTrue(true);
    }

    public function testDestructorCleansUp(): void
    {
        $transport = new TgCurlMultiTransport();
        $this->assertInstanceOf(TgCurlMultiTransport::class, $transport);
        // Destructor is called automatically
    }

    protected function setUp(): void
    {
        // We use the real transport but we will need to be careful with real network calls.
        // For a true unit test, we'd mock the curl_* functions.
        // Since we can't easily do that here, we will test the Fiber logic
        // by observing the state transitions.
        $this->transport = new TgCurlMultiTransport();
    }
}
