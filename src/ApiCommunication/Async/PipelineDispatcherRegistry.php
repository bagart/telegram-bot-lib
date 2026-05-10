<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Async;

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\AsyncFiberDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\LaravelQueueDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\PcntlDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\PcntlGrokDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\RedisQueueDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\SyncDtoPipelineDispatcher;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\DtoPipelineDispatcherContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Async\SchedulerContract;
use BAGArt\TelegramBot\Exceptions\TgAsyncException;
use BAGArt\TelegramBot\Wrappers\TgBotRedisQueueWrapper;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry\TgDispatcherNotRegistryException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry\TgWrongDispatcherRegisteredException;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

/**
 * Critical rules:
 *
 * 1. Dispatcher must never create private scheduler
 * 2. AsyncFiber dispatcher must use shared scheduler only
 * 3. Registry must support runtime scheduler injection
 * 4. Dispatcher instances must be deterministic
 */
final class PipelineDispatcherRegistry
{
    /**
     * @var array<string, class-string<DtoPipelineDispatcherContract>|DtoPipelineDispatcherContract>
     */
    private array $dispatchers = [];

    /**
     * Default registry map.
     *
     * @var array<string, class-string<DtoPipelineDispatcherContract>>
     */
    private static array $default = [
        SyncDtoPipelineDispatcher::TYPE => SyncDtoPipelineDispatcher::class,
        AsyncFiberDtoPipelineDispatcher::TYPE => AsyncFiberDtoPipelineDispatcher::class,
        LaravelQueueDtoPipelineDispatcher::TYPE => LaravelQueueDtoPipelineDispatcher::class,
        RedisQueueDtoPipelineDispatcher::TYPE => RedisQueueDtoPipelineDispatcher::class,
        PcntlDtoPipelineDispatcher::TYPE => PcntlDtoPipelineDispatcher::class,
        PcntlGrokDtoPipelineDispatcher::TYPE => PcntlGrokDtoPipelineDispatcher::class,
    ];

    public static function build(): self
    {
        $registry = new self();

        foreach (self::$default as $type => $class) {
            $registry->register(
                dispatcherClass: $class,
                type: $type,
            );
        }

        return $registry;
    }

    /**
     * @param class-string<DtoPipelineDispatcherContract>|DtoPipelineDispatcherContract $dispatcherClass
     */
    public function register(
        string|DtoPipelineDispatcherContract $dispatcherClass,
        ?string $type = null,
    ): self {
        $resolvedType = $type
            ?? (is_object($dispatcherClass)
                ? $dispatcherClass::TYPE
                : $dispatcherClass::TYPE);

        $this->dispatchers[$resolvedType] = $dispatcherClass;

        return $this;
    }

    /**
     * @return class-string<DtoPipelineDispatcherContract>
     */
    public function get(
        string $type,
    ): string {
        if (!$this->has($type)) {
            throw new TgDispatcherNotRegistryException(
                $type
            );
        }

        $dispatcher = $this->dispatchers[$type];

        return is_object($dispatcher)
            ? $dispatcher::class
            : $dispatcher;
    }

    public function has(
        string $type,
    ): bool {
        return isset(
            $this->dispatchers[$type]
        );
    }

    /**
     * Critical:
     *
     * Shared scheduler must be injected from poller layer.
     *
     * No dispatcher may own its own async runtime.
     */
    public function make(
        string $type,
        ?SchedulerContract $scheduler = null,
        ?TgBotLogWrapper $logger = null,
        ?TgBotRedisQueueWrapper $redisWrapper = null,
    ): DtoPipelineDispatcherContract {
        if (!$this->has($type)) {
            throw new TgDispatcherNotRegistryException(
                $type
            );
        }

        $dispatcher = $this->dispatchers[$type];

        if (is_object($dispatcher)) {
            return $dispatcher;
        }

        if (
            !is_a(
                $dispatcher,
                DtoPipelineDispatcherContract::class,
                true
            )
        ) {
            throw new TgWrongDispatcherRegisteredException(
                $type,
                $dispatcher,
            );
        }

        /**
         * Critical:
         * AsyncFiber dispatcher must receive shared scheduler.
         */
        if ($dispatcher === AsyncFiberDtoPipelineDispatcher::class) {
            if ($scheduler === null) {
                throw new TgAsyncException(
                    'Shared SchedulerContract is required for AsyncFiber dispatcher'
                );
            }

            $instance = new $dispatcher(
                scheduler: $scheduler,
                logger: $logger,
            );

            $this->dispatchers[$type] = $instance;

            return $instance;
        }

        /**
         * RedisQueue dispatcher must receive Redis connector.
         */
        if ($dispatcher === RedisQueueDtoPipelineDispatcher::class) {
            if ($redisWrapper === null) {
                throw new TgAsyncException(
                    'Shared TgBotRedisQueueWrapper is required for RedisQueue dispatcher'
                );
            }

            $instance = new $dispatcher(
                wrapper: $redisWrapper,
            );

            $this->dispatchers[$type] = $instance;

            return $instance;
        }

        /**
         * Other dispatchers may stay stateless.
         */
        $instance = new $dispatcher();

        $this->dispatchers[$type] = $instance;

        return $instance;
    }

    public function __destruct()
    {
        $this->dispatchers = [];
    }
}
