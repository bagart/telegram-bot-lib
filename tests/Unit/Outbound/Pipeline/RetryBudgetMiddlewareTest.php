<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundSkipException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;
use BAGArt\TelegramBot\Outbound\RetryBudgetMiddleware;

function makeBudgetTask(): OutboundTask
{
    return new OutboundTask(id: 't1', botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'), dtoClass: 'App\\SendMessage', dtoData: []);
}

function makeBudgetSpy(): array
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

describe('RetryBudgetMiddleware', function () {
    it('passes through when attempts are below the budget', function () {
        $middleware = new RetryBudgetMiddleware(maxAttempts: 5);
        $envelope = new OutboundEnvelope(makeBudgetTask(), new OutboundTaskState(attempt: 3));

        [$spy, $box] = makeBudgetSpy();
        $middleware->handle($envelope, $spy);

        expect($box->called)->toBeTrue();
    });

    it('passes through for a brand-new task (0 attempts)', function () {
        $middleware = new RetryBudgetMiddleware(maxAttempts: 5);
        $envelope = new OutboundEnvelope(makeBudgetTask(), new OutboundTaskState(attempt: 0));

        [$spy, $box] = makeBudgetSpy();
        $middleware->handle($envelope, $spy);

        expect($box->called)->toBeTrue();
    });

    it('skips when attempts reach the budget exactly', function () {
        $middleware = new RetryBudgetMiddleware(maxAttempts: 5);
        $envelope = new OutboundEnvelope(makeBudgetTask(), new OutboundTaskState(attempt: 5));

        [$spy, $box] = makeBudgetSpy();
        expect(fn () => $middleware->handle($envelope, $spy))
            ->toThrow(OutboundSkipException::class);

        expect($box->called)->toBeFalse();
    });

    it('skips when attempts exceed the budget', function () {
        $middleware = new RetryBudgetMiddleware(maxAttempts: 3);
        $envelope = new OutboundEnvelope(makeBudgetTask(), new OutboundTaskState(attempt: 10));

        expect(fn () => $middleware->handle($envelope, makeBudgetSpy()[0]))
            ->toThrow(OutboundSkipException::class);
    });

    it('skip reason is "max_attempts"', function () {
        $middleware = new RetryBudgetMiddleware(maxAttempts: 2);
        $envelope = new OutboundEnvelope(makeBudgetTask(), new OutboundTaskState(attempt: 2));

        try {
            $middleware->handle($envelope, makeBudgetSpy()[0]);
            expect('should have thrown')->toBe('threw');
        } catch (OutboundSkipException $e) {
            expect($e->reason)->toBe('max_attempts');
        }
    });
});
