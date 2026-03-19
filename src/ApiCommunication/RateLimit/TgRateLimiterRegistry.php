<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\RateLimit;

use BAGArt\ASKClient\RateLimiter\ASKRateLimiter;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices\TgRateLimiterContract;

final class TgRateLimiterRegistry
{
    /** @var array<string, class-string<TgRateLimiterContract>> */
    private static array $default = [
        ASKRateLimiter::NAME => ASKRateLimiter::class,
        TgAdvancedRateLimiter::NAME => TgAdvancedRateLimiter::class,
        TgBasicRateLimiter::NAME => TgBasicRateLimiter::class,
    ];

    /** @var array<string, class-string<TgRateLimiterContract>> */
    private array $rateLimiters = [];

    public static function build(): self
    {
        $registry = new self();

        foreach (static::$default as $type => $class) {
            $registry->register($class, $type);
        }

        return $registry;
    }

    /**
     * @param  class-string<TgRateLimiterContract>  $rateLimiterClass
     */
    public function register(
        string $rateLimiterClass,
        ?string $type = null,
    ): self {
        $this->rateLimiters[$type ?? $rateLimiterClass] = $rateLimiterClass;

        return $this;
    }

    /**
     * @return class-string<TgRateLimiterContract>
     */
    public function get(string $type): string
    {
        if (! $this->has($type)) {
            throw new \RuntimeException("Rate limiter type not registered: {$type}");
        }

        return $this->rateLimiters[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->rateLimiters[$type]);
    }

    public function make(
        ?string $type,
        ?ASKCacheWrapper $cache = null,
    ): ?TgRateLimiterContract {
        if ($type === null || ! $this->has($type)) {
            return null;
        }

        $class = $this->rateLimiters[$type];

        return match ($class) {
            TgBasicRateLimiter::class => new TgBasicRateLimiter(cache: $cache),
            TgAdvancedRateLimiter::class => new TgAdvancedRateLimiter(cache: $cache),
            default => null,
        };
    }

    public function __destruct()
    {
        $this->rateLimiters = [];
    }
}
