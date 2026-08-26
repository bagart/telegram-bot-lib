<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Modules;

/**
 * Capability kinds a module can declare. The platform uses these to know
 * what a module provides without inspecting its internals.
 */
enum TgModuleCapability: string
{
    case Processor = 'processor';
    case Command = 'command';
    case Callback = 'callback';
    case Rule = 'rule';
    case Middleware = 'middleware';
    case Cron = 'cron';
    case Ui = 'ui';
}
