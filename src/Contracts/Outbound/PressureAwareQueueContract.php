<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Outbound;

use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * Capability: pop-side pressure lanes (06 §44).
 *
 * When the runtime is under pressure, the consumer may pop only tasks at or
 * above a priority floor; lower-lane tasks stay queued (deferred, zero loss)
 * until pressure recovers.
 */
interface PressureAwareQueueContract
{
    /**
     * @param  int  $visibilityTimeoutSec  Lease duration for the popped task.
     * @param  TaskPriority  $floor  Minimum lane allowed to leave the queue now.
     */
    public function popWithLaneFloor(
        int $visibilityTimeoutSec,
        TaskPriority $floor,
    ): ?OutboundEnvelope;
}
