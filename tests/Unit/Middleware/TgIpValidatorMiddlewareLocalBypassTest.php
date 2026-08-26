<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Unit\Middleware;

use BAGArt\TelegramBot\Http\Laravel\Middlewares\TgIpValidatorMiddleware;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TgIpValidatorMiddlewareLocalBypassTest extends TestCase
{
    private Repository $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Minimal container so the config() helper resolves outside Laravel
        $this->config = new Repository([
            'telegram' => ['webhook_allow_local_ips' => false],
        ]);
        $container = new Container();
        $container->instance('config', $this->config);
        Container::setInstance($container);
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);

        parent::tearDown();
    }

    public function test_local_ip_is_rejected_by_default(): void
    {
        $this->expectException(HttpException::class);

        $this->middleware()->handle($this->request('127.0.0.1'), fn () => self::fail('must not pass'));
    }

    public function test_loopback_passes_when_bypass_enabled(): void
    {
        $this->allowLocal(true);

        $reached = false;
        $response = $this->middleware()->handle(
            $this->request('127.0.0.1'),
            function () use (&$reached) {
                $reached = true;

                return new Response('ok');
            },
        );

        $this->assertTrue($reached);
        $this->assertSame('ok', (string) $response->getContent());
    }

    public function test_public_non_telegram_ip_still_rejected_with_bypass_enabled(): void
    {
        $this->allowLocal(true);

        $this->expectException(HttpException::class);

        $this->middleware()->handle($this->request('8.8.8.8'), fn () => self::fail('must not pass'));
    }

    private function allowLocal(bool $enabled): void
    {
        $this->config->set('telegram.webhook_allow_local_ips', $enabled);
    }

    private function middleware(): TgIpValidatorMiddleware
    {
        return new TgIpValidatorMiddleware(new TelegramIpValidator());
    }

    private function request(string $ip): Request
    {
        return Request::create('/tg/', 'POST', server: ['REMOTE_ADDR' => $ip]);
    }
}
