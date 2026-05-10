<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use Psr\SimpleCache\CacheInterface;

describe('TgBotCacheWrapper', function () {
    describe('constructor', function () {
        it('creates wrapper with injected cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper)->toBeInstanceOf(TgBotCacheWrapper::class);
        });

        it('uses static cache when no cache provided', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            TgBotCacheWrapper::init($innerCache);

            $wrapper = TgBotCacheWrapper::build();

            expect($wrapper)->toBeInstanceOf(TgBotCacheWrapper::class);
        });

        it('throws exception when no cache available', function () {
            TgBotCacheWrapper::$initCache = null;

            expect(fn () => TgBotCacheWrapper::build())
                ->toThrow(\RuntimeException::class);
        });
    });

    describe('cache methods', function () {
        it('delegates get to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('get')->with('key', null)->andReturn('value');

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->get('key'))->toBe('value');
        });

        it('delegates set to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('set')->with('key', 'value', 60)->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->set('key', 'value', 60))->toBeTrue();
        });

        it('delegates put to inner cache set method', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('set')->with('key', 'value', 60)->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->put('key', 'value', 60))->toBeTrue();
        });

        it('delegates delete to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('delete')->with('key')->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->delete('key'))->toBeTrue();
        });

        it('delegates forget to inner cache delete method', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('delete')->with('key')->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->forget('key'))->toBeTrue();
        });

        it('delegates clear to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('clear')->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->clear())->toBeTrue();
        });

        it('delegates has to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('has')->with('key')->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->has('key'))->toBeTrue();
        });

        it('delegates increment to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('increment')->with('key', 1)->andReturn(1);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->increment('key'))->toBe(1);
        });

        it('delegates decrement to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('decrement')->with('key', 1)->andReturn(0);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->decrement('key'))->toBe(0);
        });

        it('delegates flush to inner cache', function () {
            $innerCache = Mockery::mock(CacheInterface::class);
            $innerCache->shouldReceive('flush')->andReturn(true);

            $wrapper = new TgBotCacheWrapper($innerCache);

            expect($wrapper->flush())->toBeTrue();
        });
    });
});
