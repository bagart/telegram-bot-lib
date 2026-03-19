<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot;

use BAGArt\AsyncKernel\Contracts\ASKSchedulerContract;
use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\Processing\ProcessingDispatcherContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry\TgDispatcherNotRegistryException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry\TgWrongDispatcherRegisteredException;
use BAGArt\TelegramBot\Exceptions\TgAsyncException;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\AsyncFiberProcessingDispatcher;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\LaravelQueueDispatcher\LaravelProcessingDispatcher;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\PcntlGrokProcessingDispatcher;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\PcntlProcessingDispatcher;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\RedisQueueProcessingDispatcher;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\SyncProcessingDispatcher;

/**
 * Critical rules:
 *
 * 1. Dispatcher must never create private scheduler
 * 2. AsyncFiber dispatcher must use shared scheduler only
 * 3. Registry must support runtime scheduler injection
 * 4. Dispatcher instances must be deterministic
 */
final class ProcessingDispatcherRegistry
{
    /**
     * @var array<string, class-string<ProcessingDispatcherContract>|ProcessingDispatcherContract>
     */
    private array $dispatchers = [];

    /**
     * Default registry map.
     *
     * @var array<string, class-string<ProcessingDispatcherContract>>
     */
    private static array $default = [
        SyncProcessingDispatcher::TYPE => SyncProcessingDispatcher::class,
        AsyncFiberProcessingDispatcher::TYPE => AsyncFiberProcessingDispatcher::class,
        LaravelProcessingDispatcher::TYPE => LaravelProcessingDispatcher::class,
        RedisQueueProcessingDispatcher::TYPE => RedisQueueProcessingDispatcher::class,
        PcntlProcessingDispatcher::TYPE => PcntlProcessingDispatcher::class,
        PcntlGrokProcessingDispatcher::TYPE => PcntlGrokProcessingDispatcher::class,
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

    public function isEmpty(): bool
    {
        return $this->dispatchers === [];
    }

    /**
     * @param class-string<ProcessingDispatcherContract>|ProcessingDispatcherContract $dispatcherClass
     */
    public function register(
        string|ProcessingDispatcherContract $dispatcherClass,
        ?string $type = null,
    ): self {
        $resolvedType = $type ?? $dispatcherClass::TYPE;

        $this->dispatchers[$resolvedType] = $dispatcherClass;

        return $this;
    }

    /**
     * @return class-string<ProcessingDispatcherContract>
     */
    public function get(
        string $type,
    ): string {
        if (!$this->has($type)) {
            throw new TgDispatcherNotRegistryException($type);
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
        string $dispatcherType,
        ?ASKSchedulerContract $scheduler = null,
        ?ASKLogWrapper $logger = null,
        ?ASKQueueAdapterContract $redisQueue = null,
        ?TgBotSetup $botSetup = null,
    ): ProcessingDispatcherContract {
        $type = $dispatcherType;

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
                ProcessingDispatcherContract::class,
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
        if ($dispatcher === AsyncFiberProcessingDispatcher::class) {
            if ($scheduler === null) {
                throw new TgAsyncException(
                    'Shared SchedulerContract is required for AsyncFiber dispatcher'
                );
            }

            $instance = new $dispatcher(
                scheduler: $scheduler,
                logger: $logger,
                botSetup: $botSetup,
            );

            $this->dispatchers[$type] = $instance;

            return $instance;
        }

        /**
         * RedisQueue dispatcher must receive Redis queue.
         */
        if ($dispatcher === RedisQueueProcessingDispatcher::class) {
            if ($redisQueue === null) {
                throw new TgAsyncException(
                    'Shared QueueContract is required for RedisQueue dispatcher'
                );
            }

            $instance = new $dispatcher(
                queue: $redisQueue,
            );

            $this->dispatchers[$type] = $instance;

            return $instance;
        }

        /**
         * Sync dispatcher receives optional logger for error reporting.
         */
        if ($dispatcher === SyncProcessingDispatcher::class) {
            $instance = new $dispatcher(
                logger: $logger,
                botSetup: $botSetup,
            );

            $this->dispatchers[$type] = $instance;

            return $instance;
        }

        if ($dispatcher === PcntlGrokProcessingDispatcher::class) {
            $instance = new $dispatcher(
                logger: $logger,
                botSetup: $botSetup,
            );

            $this->dispatchers[$type] = $instance;

            return $instance;
        }

        if ($dispatcher === PcntlProcessingDispatcher::class) {
            $instance = new $dispatcher(
                botSetup: $botSetup,
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
