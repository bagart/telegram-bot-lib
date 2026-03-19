<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

final class TgExecutionContext
{
    public function __construct(
        public string $traceId,
        public array $tags = [],
    ) {
    }
}
