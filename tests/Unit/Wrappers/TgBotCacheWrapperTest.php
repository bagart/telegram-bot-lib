<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use Psr\SimpleCache\CacheInterface;

describe('TgCacheWrapper', function () {
    describe('constructor', function () {
        it('creates wrapper with injected cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper)->toBeInstanceOf(ASKCacheWrapper::class);
        });
    });

    describe('cache methods', function () {
        it('delegates get to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('get')->with('key', null)->andReturn('value');

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->get('key'))->toBe('value');
        });

        it('delegates set to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('set')->with('key', 'value', 60)->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->set('key', 'value', 60))->toBeTrue();
        });

        it('delegates put to inner cache set method', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('set')->with('key', 'value', 60)->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->put('key', 'value', 60))->toBeTrue();
        });

        it('delegates delete to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('delete')->with('key')->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->delete('key'))->toBeTrue();
        });

        it('delegates forget to inner cache delete method', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('delete')->with('key')->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->forget('key'))->toBeTrue();
        });

        it('delegates clear to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('clear')->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->clear())->toBeTrue();
        });

        it('delegates has to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('has')->with('key')->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->has('key'))->toBeTrue();
        });

        it('delegates increment to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('increment')->with('key', 1)->andReturn(1);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->increment('key'))->toBe(1);
        });

        it('delegates decrement to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('decrement')->with('key', 1)->andReturn(0);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->decrement('key'))->toBe(0);
        });

        it('delegates flush to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('flush')->andReturn(true);

            $wrapper = new ASKCacheWrapper($innerCache);

            expect($wrapper->flush())->toBeTrue();
        });
    });
});
