<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing;

use BAGArt\TelegramBot\Processing\Execution\OrderedExecutionCoordinator;

/**
 * Named runnable unit of ordered execution. Passed into
 * {@see OrderedExecutionCoordinator::enqueue()}
 * instead of a raw closure so per-key serialized work has a type, a name in
 * stack traces, and is testable in isolation.
 */
interface OrderedTaskContract
{
    public function execute(): void;
}
