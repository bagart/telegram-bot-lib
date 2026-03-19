<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\AsyncKernel\Cache\FileCache;
use BAGArt\AsyncKernel\Cache\InMemoryCache;
use BAGArt\AsyncKernel\Cache\PhpAPCuCache;
use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\AsyncKernel\Contracts\Cache\ASKCacheContract;

final class CacheDriverRegistry
{
    /** @var array<string, class-string<ASKCacheContract>> */
    private static array $default = [
        PhpAPCuCache::TYPE => PhpAPCuCache::class,
        InMemoryCache::TYPE => InMemoryCache::class,
        FileCache::TYPE => FileCache::class,
    ];

    /** @var array<string, class-string<ASKCacheContract>> */
    private array $drivers = [];

    public static function build(): self
    {
        $registry = new self();

        foreach (static::$default as $type => $class) {
            $registry->register($class, $type);
        }

        return $registry;
    }

    /**
     * @param class-string<ASKCacheContract> $driverClass
     */
    public function register(
        string $driverClass,
        ?string $type = null,
    ): self {
        $this->drivers[$type ?? $driverClass::TYPE] = $driverClass;

        return $this;
    }

    /**
     * @return class-string<ASKCacheContract>
     */
    public function get(string $type): string
    {
        if (!$this->has($type)) {
            throw new \RuntimeException("Cache driver type not registered: {$type}");
        }

        return $this->drivers[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->drivers[$type]);
    }

    public function make(
        string $type,
        ASKClockContract $clock,
    ): ASKCacheContract {
        $class = $this->get($type);

        return $class::build($clock);
    }

    public function __destruct()
    {
        $this->drivers = [];
    }
}
