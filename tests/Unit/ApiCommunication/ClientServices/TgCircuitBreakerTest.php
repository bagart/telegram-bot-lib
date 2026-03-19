<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgCircuitBreaker;
use Psr\SimpleCache\CacheInterface;

describe('TgCircuitBreaker', function () {
    describe('canExecute()', function () {
        it('allows execution when no failures', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(0);

            expect($breaker->canExecute('sendMessage'))->toBeTrue();
        });

        it('blocks execution when threshold reached', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(5);
            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_opened_at', 0)->andReturn(time());

            expect($breaker->canExecute('sendMessage'))->toBeFalse();
        });

        it('allows execution after recovery timeout', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(5);
            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_opened_at', 0)->andReturn(time() - 60);
            $cache->shouldReceive('delete')->with('tg_circuit_sendMessage_failures')->andReturn(true);
            $cache->shouldReceive('delete')->with('tg_circuit_sendMessage_opened_at')->andReturn(true);

            expect($breaker->canExecute('sendMessage'))->toBeTrue();
        });
    });

    describe('recordFailure()', function () {
        it('increments failure count', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(0);
            $cache->shouldReceive('increment')->with('tg_circuit_sendMessage_failures', 1)->andReturn(1);
            // touch() fallback: get(key, ttl) -> set(key, value, ttl)
            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 30)->andReturn(1);
            $cache->shouldReceive('set')->with('tg_circuit_sendMessage_failures', 1, 30)->andReturn(true);
            // put(key, time(), ttl) -> set(key, value, ttl)
            $cache->shouldReceive('set')->with('tg_circuit_sendMessage_opened_at', Mockery::type('int'), 30)->andReturn(true);

            $breaker->recordFailure('sendMessage', new \RuntimeException('test error'));
        });
    });

    describe('recordSuccess()', function () {
        it('clears failure records', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('delete')->with('tg_circuit_sendMessage_failures')->andReturn(true);
            $cache->shouldReceive('delete')->with('tg_circuit_sendMessage_opened_at')->andReturn(true);

            $breaker->recordSuccess('sendMessage');
        });
    });

    describe('reset()', function () {
        it('flushes cache', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('flush')->andReturn(true);

            $breaker->reset();
        });
    });
});
