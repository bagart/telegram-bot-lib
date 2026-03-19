<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Polling;

class ProcessingStatistics
{
    public function __construct(
        public int $lastUpdateId = 0,
        public int $updateScheduled = 0,
        public int $updateProcessed = 0,
        public int $updateQueued = 0,
        public int $updateFailed = 0,
        public array $processors = [],
    ) {
    }
}
