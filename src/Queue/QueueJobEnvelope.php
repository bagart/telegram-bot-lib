<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Queue;

use BAGArt\TelegramBot\Contracts\Queue\JobHandlerContract;

/**
 * Payload stored in the queue.
 *
 * When unserialized, TgBotRedisQueueJob extracts handlerClass + handlerParams
 * and uses JobHandlerFactoryContract to build the handler.
 */
final class QueueJobEnvelope
{
    /**
     * @param  class-string<JobHandlerContract>  $handlerClass
     * @param  array<int, mixed>  $handlerParams  Positional constructor arguments
     */
    public function __construct(
        public readonly string $handlerClass,
        public readonly array $handlerParams,
    ) {
    }
}
