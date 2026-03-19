<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundRetryException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\RateLimitMiddleware;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * Hand-rolled fake OutboundRateLimiterContract.
 * Records getRetryDelay keys, returns a preset delay.
 */
class RateLimitFake implements OutboundRateLimiterContract
{
    /** @var array<string, int> key → call count */
    public array $delayCalls = [];

    public float $delayReturn = 0.0;

    public function getRetryDelay(string $key): float
    {
        $this->delayCalls[$key] = ($this->delayCalls[$key] ?? 0) + 1;

        return $this->delayReturn;
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
    }

    public function markSent(string $key): void
    {
    }
}

function makeRateLimitTask(?string $orderingKey = '12345'): OutboundTask
{
    return new OutboundTask(
        id: 't1',
        botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
        dtoClass: 'App\\TgApi\\Methods\\DTO\\SendMessageDTO',
        dtoData: [],
        orderingKey: $orderingKey,
    );
}

function makeRateLimitSpy(): array
{
    $box = new class () {
        public bool $called = false;
    };

    return [
        static function (OutboundEnvelope $e) use ($box): void {
            $box->called = true;
        },
        $box,
    ];
}

describe('RateLimitMiddleware', function () {
    it('passes through when getRetryDelay returns 0', function () {
        $fake = new RateLimitFake();
        $fake->delayReturn = 0.0;
        $middleware = new RateLimitMiddleware($fake);

        [$spy, $box] = makeRateLimitSpy();
        $middleware->handle(new OutboundEnvelope(makeRateLimitTask(), new OutboundTaskState()), $spy);

        expect($box->called)->toBeTrue();
    });

    it('throws OutboundRetryException when getRetryDelay > 0', function () {
        $fake = new RateLimitFake();
        $fake->delayReturn = 7.4; // → ceil = 8
        $middleware = new RateLimitMiddleware($fake);

        [$spy, $box] = makeRateLimitSpy();
        try {
            $middleware->handle(new OutboundEnvelope(makeRateLimitTask(), new OutboundTaskState()), $spy);
            expect('should have thrown')->toBe('threw');
        } catch (OutboundRetryException $e) {
            expect($e->delaySec)->toBe(8) // ceil(7.4)
                ->and($e->reason)->toBe('rate_limit');
        }

        expect($box->called)->toBeFalse();
    });

    it('builds the key as {botId}:{dtoMethod}:{orderingKey}', function () {
        $fake = new RateLimitFake();
        $middleware = new RateLimitMiddleware($fake);

        $middleware->handle(new OutboundEnvelope(makeRateLimitTask(), new OutboundTaskState()), makeRateLimitSpy()[0]);

        // botId=bot1, dtoMethod=basename(SendMessageDTO), orderingKey=12345.
        expect($fake->delayCalls)->toHaveKey('bot1:SendMessageDTO:12345');
    });

    it('uses "global" as the third key segment when orderingKey is null (broadcast)', function () {
        $fake = new RateLimitFake();
        $middleware = new RateLimitMiddleware($fake);

        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot2'),
            dtoClass: 'SendPhotoDTO', // no namespace — basename = full name
            dtoData: [],
            orderingKey: null,
        );

        $middleware->handle(new OutboundEnvelope($task, new OutboundTaskState()), makeRateLimitSpy()[0]);

        expect($fake->delayCalls)->toHaveKey('bot2:SendPhotoDTO:global');
    });

    it('retries with delay = ceil(retryDelay) (rounded up)', function () {
        $fake = new RateLimitFake();
        $fake->delayReturn = 0.9; // → ceil = 1
        $middleware = new RateLimitMiddleware($fake);

        try {
            $middleware->handle(new OutboundEnvelope(makeRateLimitTask(), new OutboundTaskState()), makeRateLimitSpy()[0]);
        } catch (OutboundRetryException $e) {
            expect($e->delaySec)->toBe(1);
        }
    });

    it('handles FQCN dtoClass by extracting the basename', function () {
        $fake = new RateLimitFake();
        $middleware = new RateLimitMiddleware($fake);

        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'b'),
            dtoClass: 'BAGArt\\TelegramBot\\TgApi\\Methods\\DTO\\GetUpdatesDTO',
            dtoData: [],
            priority: TaskPriority::High,
        );

        $middleware->handle(new OutboundEnvelope($task, new OutboundTaskState()), makeRateLimitSpy()[0]);

        expect($fake->delayCalls)->toHaveKey('b:GetUpdatesDTO:global');
    });
});
