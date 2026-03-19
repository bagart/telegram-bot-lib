<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\ApiCommunication\RateLimit\TgAdvancedRateLimiter;
use Psr\SimpleCache\CacheInterface;

describe('TgAdvancedRateLimiter', function () {
    it('can be instantiated with default safety margin', function () {
        $cache = Mockery::mock(CacheInterface::class);
        $wrapper = new ASKCacheWrapper($cache);
        $limiter = new TgAdvancedRateLimiter($wrapper);
        expect($limiter)->toBeInstanceOf(TgAdvancedRateLimiter::class);
    });

    it('returns delay > 0 for second request within global interval', function () {
        $cache = Mockery::mock(CacheInterface::class);
        $wrapper = new ASKCacheWrapper($cache);
        $store = [];
        $cache->shouldReceive('setMultiple')->andReturnUsing(function ($values, $ttl = null) use (&$store) {
            foreach ($values as $k => $v) {
                $store[$k] = $v;
            }
            return true;
        });
        $cache->shouldReceive('getMultiple')->andReturnUsing(function ($keys, $default = null) use (&$store) {
            return array_map(fn ($k) => $store[$k] ?? $default, $keys);
        });

        $limiter = new TgAdvancedRateLimiter($wrapper, 0.1);
        $limiter->markSent('sendMessage:12345');
        expect($limiter->getRetryDelay('sendMessage:12345'))->toBeGreaterThan(0.0);
    });

    it('respects custom safety margin (e.g. 0.5)', function () {
        $cache = Mockery::mock(CacheInterface::class);
        $wrapper = new ASKCacheWrapper($cache);
        $store = [];
        $cache->shouldReceive('setMultiple')->andReturnUsing(function ($values, $ttl = null) use (&$store) {
            foreach ($values as $k => $v) {
                $store[$k] = $v;
            }
            return true;
        });
        $cache->shouldReceive('getMultiple')->andReturnUsing(function ($keys, $default = null) use (&$store) {
            return array_map(fn ($k) => $store[$k] ?? $default, $keys);
        });

        $limiter = new TgAdvancedRateLimiter($wrapper, 0.5);
        $limiter->markSent('sendMessage:12345');
        expect($limiter->getRetryDelay('sendMessage:12345'))->toBeGreaterThan(0.0);
    });
});
