<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\ASKClient\Queue\Adapters\InMemoryQueueAdapter;
use BAGArt\ASKClientRedis\Queue\Adapters\QueueRedisAdapter;
use BAGArt\ASKClientRedis\Redis\Contract\RedisClientContract;

final class QueueAdapterRegistry
{
    /** @var array<string, class-string<ASKQueueAdapterContract>> */
    private static array $default = [
        InMemoryQueueAdapter::TYPE => InMemoryQueueAdapter::class,
        QueueRedisAdapter::TYPE => QueueRedisAdapter::class,
    ];

    /** @var array<string, class-string<ASKQueueAdapterContract>> */
    private array $adapters = [];

    public static function build(): self
    {
        $registry = new self();

        foreach (static::$default as $type => $class) {
            $registry->register($class, $type);
        }

        return $registry;
    }

    /**
     * @param class-string<ASKQueueAdapterContract> $adapterClass
     */
    public function register(
        string $adapterClass,
        ?string $type = null,
    ): self {
        $this->adapters[$type ?? $adapterClass::TYPE] = $adapterClass;

        return $this;
    }

    /**
     * @return class-string<ASKQueueAdapterContract>
     */
    public function get(string $type): string
    {
        if (!$this->has($type)) {
            throw new \RuntimeException("Queue adapter type not registered: {$type}");
        }

        return $this->adapters[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->adapters[$type]);
    }

    public function make(
        string $type,
        ?string $dsn = null,
        ?RedisClientContract $redis = null,
    ): ASKQueueAdapterContract {
        $class = $this->get($type);

        if ($redis !== null && $class === QueueRedisAdapter::class) {
            return new QueueRedisAdapter($redis);
        }

        return $class::build($dsn);
    }

    public function __destruct()
    {
        $this->adapters = [];
    }
}
