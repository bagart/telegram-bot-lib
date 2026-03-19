<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\CircuitBreakerState;
use BAGArt\TelegramBot\Outbound\OutboundCircuitBreaker;

if (!function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../Helpers.php';
}

describe('OutboundCircuitBreaker', function () {
    function cbCache(): KernelCacheAdapter
    {
        return new KernelCacheAdapter(
            makeCacheWrapper(),
            new \BAGArt\ASKClient\Lockers\InMemoryLocker(),
        );
    }

    it('starts closed for any bot', function () {
        $cb = new OutboundCircuitBreaker(cbCache());

        expect($cb->allowsRequest('bot1'))->toBeTrue()
            ->and($cb->getState('bot1'))->toBe(CircuitBreakerState::Closed);
    });

    it('opens after failureThreshold consecutive failures', function () {
        $cb = new OutboundCircuitBreaker(cbCache(), failureThreshold: 3);

        $cb->recordFailure('bot1');
        expect($cb->allowsRequest('bot1'))->toBeTrue();

        $cb->recordFailure('bot1');
        expect($cb->allowsRequest('bot1'))->toBeTrue();

        $cb->recordFailure('bot1');
        expect($cb->allowsRequest('bot1'))->toBeFalse()
            ->and($cb->getState('bot1'))->toBe(CircuitBreakerState::Open);
    });

    it('recordSuccess resets failures and closes the breaker', function () {
        $cb = new OutboundCircuitBreaker(cbCache(), failureThreshold: 2);

        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');
        expect($cb->allowsRequest('bot1'))->toBeFalse();

        $cb->recordSuccess('bot1');
        expect($cb->allowsRequest('bot1'))->toBeTrue()
            ->and($cb->getState('bot1'))->toBe(CircuitBreakerState::Closed);
    });

    it('tracks different bots independently', function () {
        $cb = new OutboundCircuitBreaker(cbCache(), failureThreshold: 2);

        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');

        expect($cb->allowsRequest('bot1'))->toBeFalse()
            ->and($cb->allowsRequest('bot2'))->toBeTrue();
    });

    it('getState returns Open for a tripped breaker', function () {
        $cb = new OutboundCircuitBreaker(cbCache(), failureThreshold: 1);

        $cb->recordFailure('bot1');

        expect($cb->getState('bot1'))->toBe(CircuitBreakerState::Open);
    });

    it('transitions to half-open after the open key expires', function () {
        $cache = cbCache();
        $cb = new OutboundCircuitBreaker($cache, failureThreshold: 1);

        $cb->recordFailure('bot1');
        expect($cb->getState('bot1'))->toBe(CircuitBreakerState::Open);

        // Simulate Open-TTL expiry: delete the state key (as Redis would do via EXPIRE).
        $cache->forget('tg_outbound:cb_state:bot1');

        expect($cb->getState('bot1'))->toBe(CircuitBreakerState::HalfOpen)
            ->and($cb->allowsRequest('bot1'))->toBeTrue();
    });

    it('half-open success closes the breaker and resets backoff', function () {
        $cache = cbCache();
        $cb = new OutboundCircuitBreaker($cache, failureThreshold: 1);

        $cb->recordFailure('bot1');
        $cache->forget('tg_outbound:cb_state:bot1'); // backoff expired → half-open

        $cb->recordSuccess('bot1');

        expect($cb->getState('bot1'))->toBe(CircuitBreakerState::Closed)
            ->and($cache->get('tg_outbound:cb_backoff:bot1'))->toBeNull();
    });

    it('half-open failure re-opens with increased backoff stage', function () {
        $cache = cbCache();
        $cb = new OutboundCircuitBreaker($cache, failureThreshold: 1);

        // First trip — backoff stage 1 (1s).
        $cb->recordFailure('bot1');
        $cache->forget('tg_outbound:cb_state:bot1'); // → half-open

        // Probe failure — re-open, stage 2.
        $cb->recordFailure('bot1');

        expect($cb->getState('bot1'))->toBe(CircuitBreakerState::Open)
            ->and($cache->get('tg_outbound:cb_backoff:bot1'))->toBe(2);
    });

    it('failures counter does not accumulate across re-opened breakers', function () {
        $cache = cbCache();
        $cb = new OutboundCircuitBreaker($cache, failureThreshold: 3);

        $cb->recordFailure('bot1');
        $cb->recordFailure('bot1');
        // Threshold not reached — not opened.
        expect($cb->allowsRequest('bot1'))->toBeTrue();

        // Success resets the counter.
        $cb->recordSuccess('bot1');
        expect($cache->get('tg_outbound:cb_failures:bot1'))->toBeNull();
    });
});
