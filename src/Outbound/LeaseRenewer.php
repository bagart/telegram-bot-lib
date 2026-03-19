<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\TelegramBot\Contracts\Outbound\LeaseRenewableQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundQueueContract;

final class LeaseRenewer implements ASKTickableContract
{
    /** @var array<string, array{envelope: OutboundEnvelope, lastRenewed: int, renewals: int}> */
    private array $tracked = [];

    public function __construct(
        private readonly OutboundQueueContract $queue,
        private readonly ASKClockContract $clock,
        private readonly int $renewIntervalSec = 30,
        private readonly int $maxRenewals = 3,
    ) {
    }

    public function track(OutboundEnvelope $envelope): void
    {
        $this->tracked[$envelope->deliveryId] = [
            'envelope' => $envelope,
            'lastRenewed' => $this->clock->time(),
            'renewals' => 0,
        ];
    }

    public function untrack(string $deliveryId): void
    {
        unset($this->tracked[$deliveryId]);
    }

    public function tick(int $systemPressure): void
    {
        if (! ($this->queue instanceof LeaseRenewableQueueContract)) {
            return;
        }

        $now = $this->clock->time();

        foreach ($this->tracked as $id => &$entry) {
            if ($now - $entry['lastRenewed'] < $this->renewIntervalSec) {
                continue;
            }

            if ($entry['renewals'] >= $this->maxRenewals) {
                throw new OutboundBusinessErrorException('lease_expired', ['deliveryId' => $id]);
            }

            $ok = $this->queue->renewLease($entry['envelope'], 60);

            if (! $ok) {
                throw new OutboundBusinessErrorException('lease_lost', ['deliveryId' => $id]);
            }

            $entry['lastRenewed'] = $now;
            $entry['renewals']++;
        }

        unset($entry);
    }

    public function pressure(): int
    {
        return count($this->tracked) > 0 ? (int) round((count($this->tracked) / 100) * 100) : 0;
    }

    public function isIdle(): bool
    {
        return $this->tracked === [];
    }

    public function queueSize(): int
    {
        return count($this->tracked);
    }
}
