<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

/**
 * CLI behavior processors for lib commands (poller/webhook/all-in-one).
 * Core processors (e.g. MessageValidatorProcessor) are always registered
 * and are NOT gated by this config.
 */
class ProcessorConfig
{
    public function __construct(
        public readonly bool $echo = false,
        public readonly bool $show = false,
        public readonly bool $log = false,
        public readonly bool $store = false,
        public readonly bool $dbg = false,
    ) {
    }
}
