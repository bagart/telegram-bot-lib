<?php

declare(strict_types=1);

use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;

/**
 * Hand-rolled PSR-16 cache (in-memory array store), wrapped in ASKCacheWrapper.
 * Implements the methods used by KernelCacheAdapter: get/set/forget.
 */
function makeCacheWrapper(): ASKCacheWrapper
{
    $psr16 = new class () implements Psr\SimpleCache\CacheInterface {
        /** @var array<string, mixed> */
        private array $store = [];

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->store[$key] ?? $default;
        }

        public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
        {
            $this->store[$key] = $value;

            return true;
        }

        public function delete(string $key): bool
        {
            unset($this->store[$key]);

            return true;
        }

        public function clear(): bool
        {
            $this->store = [];

            return true;
        }

        public function getMultiple(iterable $keys, mixed $default = null): iterable
        {
            return [];
        }

        public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
        {
            return true;
        }

        public function deleteMultiple(iterable $keys): bool
        {
            return true;
        }

        public function has(string $key): bool
        {
            return array_key_exists($key, $this->store);
        }
    };

    return new ASKCacheWrapper($psr16);
}

describe('KernelCacheAdapter', function () {
    it('get returns null for a missing key', function () {
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());

        expect($adapter->get('missing'))->toBeNull();
    });

    it('put + get round-trips a value', function () {
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());

        $adapter->put('foo', 'bar', 60);

        expect($adapter->get('foo'))->toBe('bar');
    });

    it('forget removes a key', function () {
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());

        $adapter->put('foo', 'bar', 60);
        $adapter->forget('foo');

        expect($adapter->get('foo'))->toBeNull();
    });
});

describe('KernelCacheAdapter::incrementWithTtl', function () {
    it('starts at the increment value for a new key', function () {
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());

        expect($adapter->incrementWithTtl('counter', 1, 60))->toBe(1);
    });

    it('accumulates across multiple calls', function () {
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());

        $adapter->incrementWithTtl('counter', 1, 60);
        $adapter->incrementWithTtl('counter', 1, 60);

        expect($adapter->incrementWithTtl('counter', 1, 60))->toBe(3);
    });

    it('supports increment values > 1', function () {
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), new InMemoryLocker());

        expect($adapter->incrementWithTtl('counter', 5, 60))->toBe(5)
            ->and($adapter->incrementWithTtl('counter', 5, 60))->toBe(10);
    });
});

describe('KernelCacheAdapter::lock / unlock', function () {
    it('acquires and releases a lock via the locker', function () {
        $locker = new InMemoryLocker();
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), $locker);

        expect($adapter->lock('order:1', 60, 'owner-A'))->toBeTrue()
            ->and($adapter->lock('order:1', 60, 'owner-B'))->toBeFalse();

        $adapter->unlock('order:1', 'owner-A');

        expect($adapter->lock('order:1', 60, 'owner-C'))->toBeTrue();
    });

    it('unlock with wrong owner does not release (safe release)', function () {
        $locker = new InMemoryLocker();
        $adapter = new KernelCacheAdapter(makeCacheWrapper(), $locker);

        $adapter->lock('order:1', 60, 'owner-A');
        $adapter->unlock('order:1', 'owner-B');

        expect($adapter->lock('order:1', 60, 'owner-C'))->toBeFalse();
    });
});
