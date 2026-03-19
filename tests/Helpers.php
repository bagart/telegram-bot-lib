<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;

/**
 * Hand-rollable fake clock — allows shifting time in lease expiry tests.
 */
if (!class_exists('ControllableClock')) {
    class ControllableClock implements ASKClockContract
    {
        public int $time;

        public function __construct(int $startTime = 1000000)
        {
            $this->time = $startTime;
        }

        public function advance(int $seconds): void
        {
            $this->time += $seconds;
        }

        public function microtime(): float
        {
            return (float)$this->time;
        }

        public function time(): int
        {
            return $this->time;
        }

        public function timeMs(): int
        {
            return $this->time * 1000;
        }

        public function hrtime(): int
        {
            return $this->time * ASKClockContract::NS_PER_SEC;
        }

        public function sleep(int $microseconds): void
        {
            $this->advance((int)($microseconds / 1_000_000));
        }

        public function getSecondsFromInterval(DateInterval $interval): int
        {
            return 0;
        }
    }
}

/**
 * Hand-rolled PSR-16 cache (in-memory array store), wrapped in ASKCacheWrapper.
 * Implements the methods used by KernelCacheAdapter: get/set/forget.
 */
if (!function_exists('makeCacheWrapper')) {
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
}
