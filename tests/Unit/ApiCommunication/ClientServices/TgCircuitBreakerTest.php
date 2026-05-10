<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgCircuitBreaker;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use Psr\SimpleCache\CacheInterface;

describe('TgCircuitBreaker', function () {
    describe('canExecute()', function () {
        it('allows execution when no failures', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(0);

            expect($breaker->canExecute('sendMessage'))->toBeTrue();
        });

        it('blocks execution when threshold reached', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(5);
            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_opened_at', 0)->andReturn(time());

            expect($breaker->canExecute('sendMessage'))->toBeFalse();
        });

        it('allows execution after recovery timeout', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
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
            $wrapper = new TgBotCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('get')->with('tg_circuit_sendMessage_failures', 0)->andReturn(0);
            $cache->shouldReceive('set')->with('tg_circuit_sendMessage_failures', 1, 30)->andReturn(true);
            $cache->shouldReceive('set')->with('tg_circuit_sendMessage_opened_at', Mockery::type('int'), 30)->andReturn(
                true
            );

            $breaker->recordFailure('sendMessage');

            expect(true)->toBeTrue();
        });
    });

    describe('recordSuccess()', function () {
        it('clears failure records', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('delete')->with('tg_circuit_sendMessage_failures')->andReturn(true);
            $cache->shouldReceive('delete')->with('tg_circuit_sendMessage_opened_at')->andReturn(true);

            $breaker->recordSuccess('sendMessage');

            expect(true)->toBeTrue();
        });
    });

    describe('reset()', function () {
        it('flushes cache', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $breaker = new TgCircuitBreaker($wrapper);

            $cache->shouldReceive('flush')->andReturn(true);

            $breaker->reset();

            expect(true)->toBeTrue();
        });
    });
});
