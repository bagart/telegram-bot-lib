<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ErrorHandling;

use BAGArt\TelegramBot\Contracts\Processing\ProcessingErrorActionContract;

final class ProcessingErrorRegistry
{
    /**
     * @var ProcessingErrorActionContract
     */
    private array $rules = [];

    /**
     * @var list<ProcessingErrorActionContract>
     */
    private array $defaults = [];

    public function register(
        string $exceptionClass,
        ProcessingErrorActionContract ...$actions,
    ): void {
        $this->rules[$exceptionClass] = array_values($actions);
    }

    public function setDefaults(ProcessingErrorActionContract ...$actions): self
    {
        $this->defaults = array_values($actions);

        return $this;
    }

    /**
     * @return list<ProcessingErrorActionContract>
     */
    public function resolve(\Throwable $e): array
    {
        foreach ($this->rules as $class => $actions) {
            if ($e instanceof $class) {
                return $actions;
            }
        }

        return $this->defaults;
    }
}
