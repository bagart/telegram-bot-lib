<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Tests\Support;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\PressureAwareQueueContract;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\TaskPriority;

/**
 * Test fake: records lane floors passed by the daemon, never yields tasks.
 *
 * @internal
 */
final class RecordingLaneQueue implements OutboundQueueContract, PressureAwareQueueContract
{
    /** @var list<TaskPriority> */
    public array $floors = [];

    /** @param  list<TaskPriority>  $calls  Out param collecting observed floors. */
    public function __construct(private array &$calls)
    {
    }

    public function push(OutboundTask $task): void
    {
    }

    public function pop(int $visibilityTimeoutSec = 60): ?OutboundEnvelope
    {
        return null;
    }

    public function popWithLaneFloor(int $visibilityTimeoutSec, TaskPriority $floor): ?OutboundEnvelope
    {
        $this->calls[] = $floor;
        $this->floors[] = $floor;

        return null;
    }

    public function ack(OutboundEnvelope $envelope): void
    {
    }

    public function release(OutboundEnvelope $envelope, int $delaySec): void
    {
    }

    public function size(): int
    {
        return 0;
    }
}
