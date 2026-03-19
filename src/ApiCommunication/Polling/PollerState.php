<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Polling;

final class PollerState
{
    public function __construct(
        public bool $runed = false,
        public ?int $lastUpdateId = null,
        public ?int $lastAckedUpdatedId = null,
        public bool $pollerIsPromised = false,
        public int $nextPollAfter = 0,
        public bool $shutdown = false,
        public int $ackRetries = 0,
        public bool $ackInProgress = false,
    ) {
    }

    public function offset(): int
    {
        return $this->lastUpdateId ? $this->lastUpdateId + 1 : 0;
    }

    public function advance(
        int $updateId,
    ): void {
        $this->lastUpdateId = max(
            $this->lastUpdateId,
            $updateId,
        );
    }
}
