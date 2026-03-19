<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\ASKClock;
use BAGArt\AsyncKernel\Cache\FileCache;

describe('TinyFileCacheDriver', function () {
    $cacheDir = sys_get_temp_dir().'/tg_test_cache_'.uniqid();
    $clock = new ASKClock();

    beforeEach(function () use ($cacheDir) {
        if (is_dir($cacheDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
            rmdir($cacheDir);
        }
    });

    afterEach(function () use ($cacheDir) {
        if (is_dir($cacheDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
            rmdir($cacheDir);
        }
    });

    describe('set() and get()', function () use ($cacheDir, $clock) {
        it('stores and retrieves value', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('test_key', 'test_value', 3600);

            expect($cache->get('test_key'))->toBe('test_value');
        });

        it('returns default for non-existent key', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            expect($cache->get('non_existent', 'default'))->toBe('default');
        });

        it('stores and retrieves array value', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('array_key', ['a' => 1, 'b' => 2], 3600);

            expect($cache->get('array_key'))->toBe(['a' => 1, 'b' => 2]);
        });

        it('stores and retrieves integer value', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('int_key', 42, 3600);

            expect($cache->get('int_key'))->toBe(42);
        });
    });

    describe('has()', function () use ($cacheDir, $clock) {
        it('returns true for existing key', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('test_key', 'value', 3600);

            expect($cache->has('test_key'))->toBeTrue();
        });

        it('returns false for non-existent key', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            expect($cache->has('non_existent'))->toBeFalse();
        });
    });

    describe('delete()', function () use ($cacheDir, $clock) {
        it('deletes existing key', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('test_key', 'value', 3600);
            $result = $cache->delete('test_key');

            expect($result)->toBeTrue();
            expect($cache->has('test_key'))->toBeFalse();
        });

        it('returns true for non-existent key', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            expect($cache->delete('non_existent'))->toBeTrue();
        });
    });

    describe('clear()', function () use ($cacheDir, $clock) {
        it('clears all cached items', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('key1', 'value1', 3600);
            $cache->set('key2', 'value2', 3600);

            $result = $cache->clear();

            expect($result)->toBeTrue();
            expect($cache->has('key1'))->toBeFalse();
            expect($cache->has('key2'))->toBeFalse();
        });
    });

    describe('increment()', function () use ($cacheDir, $clock) {
        it('increments existing value', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('counter', 5, 3600);
            $result = $cache->increment('counter', 3);

            expect($result)->toBe(8);
        });

        it('creates new value when key does not exist', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $result = $cache->increment('new_counter', 5);

            expect($result)->toBe(5);
        });
    });

    describe('TTL expiration', function () use ($cacheDir, $clock) {
        it('returns default for expired key', function () use ($cacheDir, $clock) {
            $cache = new FileCache($clock, $cacheDir);

            $cache->set('expired_key', 'value', 1);

            sleep(2);

            expect($cache->get('expired_key', 'default'))->toBe('default');
        });
    });
});
