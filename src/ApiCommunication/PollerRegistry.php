<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\Pollers\AsyncPoller;
use BAGArt\TelegramBot\ApiCommunication\Pollers\SyncPoller;
use BAGArt\TelegramBot\Contracts\ApiCommunication\Pollers\PollerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\Registry\TgPollerNotRegistryException;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotOutputWrapper;

final class PollerRegistry
{
    /** @var array<string, class-string<PollerContract>|PollerContract> */
    private array $pollers = [];

    private static array $default = [
        SyncPoller::TYPE => SyncPoller::class,
        AsyncPoller::TYPE => AsyncPoller::class,
    ];

    public static function build(): self
    {
        $registry = new self();
        foreach (static::$default as $type => $class) {
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
        ?TgBotApiDTOClientContract $dtoClient = null,
        ?TgBotOutputWrapper $output = null,
    ): PollerContract {
        if (!$this->has($type)) {
            throw new TgPollerNotRegistryException($type);
        }

        /** @var PollerContract|SyncPoller|AsyncPoller $pollerClass */
        $pollerClass = $this->pollers[$type];

        if (is_object($pollerClass)) {
            return $pollerClass;
        }

        return $pollerClass::build(
            updateProcessor: $updateProcessor,
            dtoClient: $dtoClient,
            output: $output,
        );
    }

    public function __destruct()
    {
        $this->pollers = [];
    }
}
