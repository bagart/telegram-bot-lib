<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Processors;

use BAGArt\ASKClient\Contracts\Queue\ASKQueueAdapterContract;

interface QueueAwareProcessorContract
{
    public function setQueue(ASKQueueAdapterContract $queue): void;
}
