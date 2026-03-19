<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\RateLimiter\RateLimiterContract;
use BAGArt\TelegramBot\Outbound\Adapters\OutboundRateLimiterAdapter;

/**
 * Hand-rolled fake RateLimiterContract — records calls, returns presets.
 * No Mockery (project convention).
 */
class FakeRateLimiter implements RateLimiterContract
{
    public array $calls = [];

    public float $retryDelayReturn = 0.0;

    public function getRetryDelay(string $key): float
    {
        $this->calls['getRetryDelay'][$key] = ($this->calls['getRetryDelay'][$key] ?? 0) + 1;

        return $this->retryDelayReturn;
    }

    public function markSent(string $key): void
    {
        $this->calls['markSent'][$key] = ($this->calls['markSent'][$key] ?? 0) + 1;
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
        $this->calls['registerRetryAfter'][$key] = $seconds;
    }

    public function reset(string $key): void
    {
        $this->calls['reset'][$key] = true;
    }
}

describe('OutboundRateLimiterAdapter', function () {
    it('delegates getRetryDelay to the underlying limiter', function () {
        $fake = new FakeRateLimiter();
        $fake->retryDelayReturn = 5.0;
        $adapter = new OutboundRateLimiterAdapter($fake);

        expect($adapter->getRetryDelay('bot1:sendMessage:123'))->toBe(5.0)
            ->and($fake->calls['getRetryDelay'])->toHaveKey('bot1:sendMessage:123');
    });

    it('delegates registerRetryAfter', function () {
        $fake = new FakeRateLimiter();
        $adapter = new OutboundRateLimiterAdapter($fake);

        $adapter->registerRetryAfter('bot1:sendMessage:123', 30.0);

        expect($fake->calls['registerRetryAfter']['bot1:sendMessage:123'])->toBe(30.0);
    });

    it('delegates markSent', function () {
        $fake = new FakeRateLimiter();
        $adapter = new OutboundRateLimiterAdapter($fake);

        $adapter->markSent('bot1:sendMessage:123');

        expect($fake->calls['markSent']['bot1:sendMessage:123'])->toBe(1);
    });

    it('passes the key verbatim (no transformation)', function () {
        $fake = new FakeRateLimiter();
        $adapter = new OutboundRateLimiterAdapter($fake);

        $adapter->getRetryDelay('complex:key:with:parts');

        // Key is passed as opaque — adapter does not parse.
        expect($fake->calls['getRetryDelay'])->toHaveKey('complex:key:with:parts');
    });
});
