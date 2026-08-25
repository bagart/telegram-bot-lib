<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Update;

use BAGArt\TelegramBot\Contracts\Processing\OrderedTaskContract;

/**
 * Ordered task wrapping one update's full processing cycle. Created by
 * {@see UpdateRouter::dispatch()}; the router stays the owner of processing
 * logic, the task only binds it to a concrete context.
 */
final class ProcessUpdateTask implements OrderedTaskContract
{
    public function __construct(
        private readonly UpdateRouter $router,
        private readonly UpdateContext $updateContext,
    ) {}

    public function execute(): void
    {
        $this->router->runProcess($this->updateContext);
    }
}
