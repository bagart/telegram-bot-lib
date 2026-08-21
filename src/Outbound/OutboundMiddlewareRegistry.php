<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

/**
 * Registry of module-declared outbound middleware (class-strings).
 * Instances are built lazily on first use (no-arg constructors) and cached
 * for the process lifetime. Mirrors the MessageValidationRuleRegistry pattern.
 */
final class OutboundMiddlewareRegistry
{
    /** @var array<class-string<OutboundMiddleware>, true> */
    private array $classes = [];

    /** @var array<class-string<OutboundMiddleware>, OutboundMiddleware> */
    private array $instances = [];

    /**
     * @param  class-string<OutboundMiddleware>  $middlewareClass
     */
    public function registerClass(string $middlewareClass): self
    {
        $this->classes[$middlewareClass] = true;

        return $this;
    }

    public function isRegistered(string $middlewareClass): bool
    {
        return isset($this->classes[$middlewareClass]);
    }

    /**
     * @return list<class-string<OutboundMiddleware>>
     */
    public function classes(): array
    {
        return array_keys($this->classes);
    }

    /**
     * Lazily built middleware instances, in registration order.
     *
     * @return list<OutboundMiddleware>
     */
    public function middlewares(): array
    {
        $middlewares = [];
        foreach ($this->classes() as $class) {
            $middlewares[] = $this->instances[$class] ??= new $class();
        }

        return $middlewares;
    }
}
