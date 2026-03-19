<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Observability;

final class TgMetricsCollector
{
    /** @var array<string, list<float>> */
    private array $metrics = [];

    public function record(string $op, float $duration): void
    {
        $this->metrics[$op][] = $duration;
    }

    /** @return array<string, list<float>> */
    public function all(): array
    {
        return $this->metrics;
    }
}
