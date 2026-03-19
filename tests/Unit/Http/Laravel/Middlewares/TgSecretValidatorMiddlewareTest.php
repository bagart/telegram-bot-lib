<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\BotServices\TgBotsSecretServiceContract;
use BAGArt\TelegramBot\Exceptions\TgBotInvalidSecretException;
use BAGArt\TelegramBot\Http\Laravel\Middlewares\TgSecretValidatorMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

describe('TgSecretValidatorMiddleware', function () {
    function createRequest(?string $secret): Request
    {
        $request = Request::create('/tg', 'POST');
        if ($secret !== null) {
            $request->headers->set('X-Telegram-Bot-Api-Secret-Token', $secret);
        }

        return $request;
    }

    describe('handle()', function () {
        it('aborts 401 when secret header is missing', function () {
            $middleware = new TgSecretValidatorMiddleware(
                Mockery::mock(TgBotsSecretServiceContract::class),
            );

            $middleware->handle(createRequest(null), fn () => throw new \RuntimeException('Should not reach'));
        })->throws(HttpException::class, 'Unauthorized: missing secret token');

        it('aborts 401 when secret header is empty', function () {
            $middleware = new TgSecretValidatorMiddleware(
                Mockery::mock(TgBotsSecretServiceContract::class),
            );

            $middleware->handle(createRequest(''), fn () => throw new \RuntimeException('Should not reach'));
        })->throws(HttpException::class, 'Unauthorized: missing secret token');

        it('aborts 403 when secret is invalid', function () {
            $secretService = Mockery::mock(TgBotsSecretServiceContract::class);
            $secretService->shouldReceive('botId')
                ->with('123:badhash')
                ->andThrow(new TgBotInvalidSecretException('Invalid secret'));

            $middleware = new TgSecretValidatorMiddleware($secretService);

            $middleware->handle(createRequest('123:badhash'), fn () => throw new \RuntimeException('Should not reach'));
        })->throws(HttpException::class, 'Forbidden: invalid secret token');

        it('passes through and binds TgBotConfig for valid secret', function () {
            $secretService = Mockery::mock(TgBotsSecretServiceContract::class);
            $secretService->shouldReceive('botId')
                ->with('123:validhash')
                ->andReturn('123');

            $middleware = new TgSecretValidatorMiddleware($secretService);

            $nextCalled = false;
            $next = function (Request $request) use (&$nextCalled): Response {
                $nextCalled = true;

                $botConfig = app(TgBotConfig::class);
                expect($botConfig)->toBeInstanceOf(TgBotConfig::class);
                expect($botConfig->botId)->toBe('123');

                return new Response('ok');
            };

            $response = $middleware->handle(createRequest('123:validhash'), $next);

            expect($nextCalled)->toBeTrue();
            expect($response->getContent())->toBe('ok');
        });

        it('uses the botId from the secret service', function () {
            $secretService = Mockery::mock(TgBotsSecretServiceContract::class);
            $secretService->shouldReceive('botId')
                ->with('987654321:otherhash')
                ->andReturn('987654321');

            $middleware = new TgSecretValidatorMiddleware($secretService);

            $nextCalled = false;
            $next = function () use (&$nextCalled): Response {
                $nextCalled = true;

                expect(app(TgBotConfig::class)->botId)->toBe('987654321');

                return new Response('ok');
            };

            $middleware->handle(createRequest('987654321:otherhash'), $next);

            expect($nextCalled)->toBeTrue();
        });
    });
});
