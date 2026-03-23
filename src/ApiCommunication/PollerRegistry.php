<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\Pollers\AsyncPoller;
use BAGArt\TelegramBot\ApiCommunication\Pollers\SyncPoller;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry\TgPollerNotRegistryException;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;

final class PollerRegistry
{
    /** @var array<string, class-string<PollerContract>|PollerContract> */
    private array $pollers = [];

    public static function build(): self
    {
        if (function_exists('app')) {
            return app(static::class);
        }

        $registry = new self();
        foreach (
            [
                SyncPoller::TYPE => SyncPoller::class,
                AsyncPoller::TYPE => AsyncPoller::class,
            ] as $type => $class
        ) {
            $registry->register($class, $type);
        }

        return $registry;
    }

    /**
     * @param  class-string<PollerContract>|PollerContract  $pollerClass
     */
    public function register(
        string|PollerContract $pollerClass,
        ?string $type = null
    ): self {
        $this->pollers[$type ?? $pollerClass::TYPE] = $pollerClass;

        return $this;
    }

    /**
     * @return class-string<PollerContract>|PollerContract
     */
    public function get(string $type): string|PollerContract
    {
        if (!$this->has($type)) {
            throw new TgPollerNotRegistryException($type);
        }

        $poller = $this->pollers[$type];

        return is_object($poller) ? $poller::class : $poller;
    }

    public function has(string $type): bool
    {
        return isset($this->pollers[$type]);
    }

    /**
     * @param  array<string, mixed>  $constructorArgs
     */
    public function make(
        string $type,
        UpdateDTOInitProcessor $updateProcessor,
        ?TgBotApiDTOClient $dtoClient = null,
    ): PollerContract {
        if (!$this->has($type)) {
            throw new TgPollerNotRegistryException($type);
        }

        /** @var PollerContract|SyncPoller|AsyncPoller $pollerClass */
        $pollerClass = $this->pollers[$type];

        if (is_object($pollerClass)) {
            return $pollerClass;
        }

        return new $pollerClass(
            dtoClient: $dtoClient ?? TgBotApiDTOClient::build(),
            updateProcessor: $updateProcessor,
        );
    }

    public function __destruct()
    {
        $this->pollers = [];
    }
}
