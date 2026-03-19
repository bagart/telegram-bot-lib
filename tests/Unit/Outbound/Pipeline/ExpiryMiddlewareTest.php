<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Outbound\ExpiryMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundSkipException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;

/** Hand-rollable fake clock for ExpiryMiddleware. */
class ExpiryClock implements ASKClockContract
{
    public function __construct(public int $time = 1000000)
    {
    }

    public function advance(int $seconds): void
    {
        $this->time += $seconds;
    }

    public function microtime(): float
    {
        return (float)$this->time;
    }

    public function time(): int
    {
        return $this->time;
    }

    public function timeMs(): int
    {
        return $this->time * 1000;
    }

    public function hrtime(): int
    {
        return $this->time * ASKClockContract::NS_PER_SEC;
    }

    public function sleep(int $microseconds): void
    {
    }

    public function getSecondsFromInterval(DateInterval $interval): int
    {
        return 0;
    }
}

/** Spy $next: returns [closure, box]; box->called — whether it was called. */
function makeNextSpy(): array
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

function makeTaskForExpiry(int $createdAtOffset): OutboundTask
{
    // createdAt = now - offset; age(envelope) is computed relative to clock->time().
    $createdAt = (new DateTimeImmutable())->setTimestamp(1000000 - $createdAtOffset);

    return new OutboundTask(
        id: 't1',
        botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
        dtoClass: 'App\\SendMessage',
        dtoData: [],
        createdAt: $createdAt,
    );
}

function makeEnvelopeForExpiry(int $attempt): OutboundEnvelope
{
    return new OutboundEnvelope(
        task: new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\SendMessage',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000),
        ),
        state: new OutboundTaskState(attempt: $attempt),
    );
}

describe('ExpiryMiddleware', function () {
    it('passes through a fresh task with few attempts', function () {
        $clock = new ExpiryClock(1000000);
        $middleware = new ExpiryMiddleware(maxAgeSec: 3600, minAttemptsForExpiry: 2, clock: $clock);

        // Task created 'now' (age = 0), 0 attempts → should pass.
        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\Send',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000),
        );
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(attempt: 0));

        [$spy, $box] = makeNextSpy();
        $middleware->handle($envelope, $spy);

        expect($box->called)->toBeTrue();
    });

    it('skips a task older than maxAge with enough attempts', function () {
        $clock = new ExpiryClock(1000000);
        // maxAge=3600, minAttempts=2 → task age 4000 (> 3600) with 2 attempts — skip.
        $middleware = new ExpiryMiddleware(maxAgeSec: 3600, minAttemptsForExpiry: 2, clock: $clock);

        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\Send',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000 - 4000),
        );
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(attempt: 2));

        [$spy, $box] = makeNextSpy();
        expect(fn () => $middleware->handle($envelope, $spy))
            ->toThrow(OutboundSkipException::class);

        expect($box->called)->toBeFalse();
    });

    it('does NOT skip an old task if it has fewer than minAttempts', function () {
        $clock = new ExpiryClock(1000000);
        $middleware = new ExpiryMiddleware(maxAgeSec: 3600, minAttemptsForExpiry: 2, clock: $clock);

        // Old (age 4000 > 3600), but only 1 attempt (< minAttempts=2) → give a chance.
        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\Send',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000 - 4000),
        );
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(attempt: 1));

        [$spy, $box] = makeNextSpy();
        $middleware->handle($envelope, $spy);

        expect($box->called)->toBeTrue();
    });

    it('does NOT skip a task with many attempts if it is still young', function () {
        $clock = new ExpiryClock(1000000);
        $middleware = new ExpiryMiddleware(maxAgeSec: 3600, minAttemptsForExpiry: 2, clock: $clock);

        // Fresh (age 100 < 3600), but 5 attempts → not yet expired by age.
        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\Send',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000 - 100),
        );
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(attempt: 5));

        [$spy, $box] = makeNextSpy();
        $middleware->handle($envelope, $spy);

        expect($box->called)->toBeTrue();
    });

    it('uses the injected clock for age computation (not wall-clock)', function () {
        // clock ahead of createdAt → age is positive and large.
        $clock = new ExpiryClock(1000100); // now = 1000100, createdAt = 1000000 → age = 100
        $middleware = new ExpiryMiddleware(maxAgeSec: 50, minAttemptsForExpiry: 2, clock: $clock);

        // age = 100 > 50, attempts=2 → skip.
        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\Send',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000),
        );
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(attempt: 2));

        expect(fn () => $middleware->handle($envelope, makeNextSpy()[0]))
            ->toThrow(OutboundSkipException::class);
    });

    it('skip reason is "expired"', function () {
        $clock = new ExpiryClock(1000000);
        $middleware = new ExpiryMiddleware(maxAgeSec: 1, minAttemptsForExpiry: 1, clock: $clock);

        $task = new OutboundTask(
            id: 't1',
            botConfig: new TgBotConfig(token: 'test:token', botId: 'bot1'),
            dtoClass: 'App\\Send',
            dtoData: [],
            createdAt: (new DateTimeImmutable())->setTimestamp(1000000 - 100),
        );
        $envelope = new OutboundEnvelope($task, new OutboundTaskState(attempt: 1));

        try {
            $middleware->handle($envelope, makeNextSpy()[0]);
            expect('should have thrown')->toBe('threw');
        } catch (OutboundSkipException $e) {
            expect($e->reason)->toBe('expired');
        }
    });
});
