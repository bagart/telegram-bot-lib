<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgBasicRateLimiter;
use Psr\SimpleCache\CacheInterface;

describe('TgRateLimiter', function () {
    describe('getRetryDelay()', function () {
        it('returns 0 when under limit', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $limiter = new TgBasicRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_basic:flood:test', null)->andReturn(null);

            expect($limiter->getRetryDelay('test'))->toBe(0.0);
        });

        it('returns delay when at limit', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $limiter = new TgBasicRateLimiter($wrapper);

            $cache->shouldReceive('get')->with('tg_basic:flood:test', null)->andReturn(microtime(true) + 10);

            expect($limiter->getRetryDelay('test'))->toBeGreaterThan(0);
        });

        it('returns 0 after markSent then reset', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $limiter = new TgBasicRateLimiter($wrapper);

            $limiter->markSent('test');

            $cache->shouldReceive('delete')->with('tg_basic:flood:test')->once()->andReturn(true);
            $limiter->reset('test');

            $cache->shouldReceive('get')->with('tg_basic:flood:test', null)->once()->andReturn(null);
            expect($limiter->getRetryDelay('test'))->toBe(0.0);
        });
    });

    describe('registerRetryAfter()', function () {
        it('blocks subsequent requests', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $limiter = new TgBasicRateLimiter($wrapper);

            $cache->shouldReceive('set')->with(
                'tg_basic:flood:test',
                Mockery::on(fn ($val) => is_float($val) && $val > microtime(true)),
                Mockery::on(fn ($ttl) => is_int($ttl) && $ttl === 10),
            )->once()->andReturn(true);

            $limiter->registerRetryAfter('test', 5.0);
        });
    });

    describe('markSent()', function () {
        it('is a no-op and does not throw', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $limiter = new TgBasicRateLimiter($wrapper);

            $limiter->markSent('test');
            expect(true)->toBeTrue();
        });
    });

    describe('reset()', function () {
        it('resets all state for key', function () {
            $cache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($cache);
            $limiter = new TgBasicRateLimiter($wrapper);

            $cache->shouldReceive('delete')->with('tg_basic:flood:test')->andReturn(true);

            $limiter->reset('test');
            expect(true)->toBeTrue();
        });
    });
});
