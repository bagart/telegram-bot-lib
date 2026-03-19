<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Queue;

use BAGArt\TelegramBot\Contracts\Queue\JobHandlerContract;
use BAGArt\TelegramBot\Contracts\Queue\JobHandlerFactoryContract;

final class JobHandlerFactory implements JobHandlerFactoryContract
{
    /** @var array<class-string<JobHandlerContract>, JobHandlerContract> */
    private array $cache = [];

    public function __construct(
        private readonly bool $allowCache = true,
    ) {
    }

    public function build(string $class, array $params = []): JobHandlerContract
    {
        $cacheKey = $class.serialize($params);

        if ($this->allowCache && isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $handler = new $class(...$params);

        assert($handler instanceof JobHandlerContract);

        if ($this->allowCache) {
            $this->cache[$cacheKey] = $handler;
        }

        return $handler;
    }
}
