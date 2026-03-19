<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Observability;

final class TgTraceSpan
{
    public function __construct(
        public readonly string $traceId,
        public readonly string $operation,
        public readonly float $startTime,
    ) {
    }

    public function finish(): array
    {
        return [
            'traceId' => $this->traceId,
            'duration' => microtime(true) - $this->startTime,
        ];
    }
}
