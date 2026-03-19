<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\ASKClientRedis\Redis\Contract\RedisClientContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundOrderingQueueContract;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\Adapters\RedisOutboundQueueContractContractContractContract;

final class OutboundQueueRegistry
{
    /** @var array<string, class-string<OutboundOrderingQueueContract>> */
    private static array $default = [
        InMemoryOutboundQueue::TYPE => InMemoryOutboundQueue::class,
        RedisOutboundQueueContractContractContractContract::TYPE => RedisOutboundQueueContractContractContractContract::class,
    ];

    /** @var array<string, class-string<OutboundOrderingQueueContract>> */
    private array $queues = [];

    public static function build(): self
    {
        $registry = new self();

        foreach (static::$default as $type => $class) {
            $registry->register($class, $type);
        }

        return $registry;
    }

    /**
     * @param class-string<OutboundOrderingQueueContract> $queueClass
     */
    public function register(
        string $queueClass,
        ?string $type = null,
    ): self {
        $this->queues[$type ?? $queueClass::TYPE] = $queueClass;

        return $this;
    }

    /**
     * @return class-string<OutboundOrderingQueueContract>
     */
    public function get(string $type): string
    {
        if (!$this->has($type)) {
            throw new \RuntimeException("Outbound queue type not registered: {$type}");
        }

        return $this->queues[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->queues[$type]);
    }

    public function make(
        string $type,
        ASKClockContract $clock,
        ?string $dsn = null,
        int $maxSize = 10000,
        bool $useLuaOptimization = true,
        ?RedisClientContract $redis = null,
    ): OutboundOrderingQueueContract {
        $class = $this->get($type);

        if ($redis !== null && $class === RedisOutboundQueueContractContractContractContract::class) {
            return new RedisOutboundQueueContractContractContractContract($redis, $clock, $useLuaOptimization);
        }

        return $class::build($clock, $dsn, $maxSize, $useLuaOptimization);
    }

    public function __destruct()
    {
        $this->queues = [];
    }
}
