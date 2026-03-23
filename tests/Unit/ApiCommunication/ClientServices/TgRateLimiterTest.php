<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\ClientServices\TgRateLimiter;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use Psr\SimpleCache\CacheInterface;

describe('TgRateLimiter', function () {
    describe('acquire()', function () {
        it('allows requests within limit', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_rate_limit_test', 0)->andReturn(0);
            $cache->shouldReceive('increment')->with('tg_rate_limit_test', 1)->andReturn(1);
            $cache->shouldReceive('set')->with('tg_rate_limit_test', 1, 60)->andReturn(true);

            expect($limiter->acquire('test'))->toBeTrue();
        });

        it('denies requests over limit', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_rate_limit_test', 0)->andReturn(50);

            expect($limiter->acquire('test'))->toBeFalse();
        });

        it('allows multiple tokens when under limit', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_rate_limit_test', 0)->andReturn(0);
            $cache->shouldReceive('increment')->with('tg_rate_limit_test', 5)->andReturn(5);
            $cache->shouldReceive('set')->with('tg_rate_limit_test', 5, 60)->andReturn(true);

            expect($limiter->acquire('test', 5))->toBeTrue();
        });
    });

    describe('available()', function () {
        it('returns correct available count', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_rate_limit_test', 0)->andReturn(10);

            expect($limiter->available('test'))->toBe(40);
        });

        it('returns 0 when at limit', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_rate_limit_test', 0)->andReturn(50);

            expect($limiter->available('test'))->toBe(0);
        });

        it('returns max when empty', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_rate_limit_test', 0)->andReturn(0);

            expect($limiter->available('test'))->toBe(50);
        });
    });

    describe('reset()', function () {
        it('resets rate limit for key', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($cache);
            $limiter = new TgRateLimiter($wrapper);

            $cache->shouldReceive('delete')->with('tg_rate_limit_test')->andReturn(true);

            $limiter->reset('test');

            expect(true)->toBeTrue();
        });
    });
});
