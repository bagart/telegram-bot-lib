<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\TelegramBot\Contracts\Outbound\AtomicDlqQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\ChannelDiscoverableQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\LeaseRenewableQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundOrderingQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\PurgeableQueueContract;
use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\OutboundBackpressureException;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;

/**
 * In-memory outbound queue implementation — all 6 interfaces.
 *
 * For tests and standalone quickstart. Bounded: maxSize limits total size
 * (queues + delayed + inflight + global); on overflow, push throws
 * {@see OutboundBackpressureException}.
 *
 * Structures mirror RedisOutboundQueueContractContractContractContract:
 *   - queues:     array<string, list<string>> [orderingKey => [envelopeJson, ...]]
 *   - readyKeys:  array<string, int> [orderingKey => priority] — sorted via asort
 *   - delayed:    array<string, int> [deliveryId => availableAt]
 *   - delayedData: array<string, string> [deliveryId => envelopeJson]
 *   - inflight:   array<string, array{orderingKey, envelopeJson, leaseExpiry}>
 *   - global:     array<string, int> [envelopeJson => priority] — sorted via asort
 *   - dlq:        array<string, array<string, string>> [channel => [entryId => entryJson]]
 */
final class InMemoryOutboundQueue implements AtomicDlqQueueContract, ChannelDiscoverableQueueContract, LeaseRenewableQueueContract, OutboundOrderingQueueContract, PurgeableQueueContract
{
    public const string TYPE = 'in_memory';

    private const string QUEUE_NAME = 'tg-outbound';

    private const string DLQ_PREFIX = 'tg-dlq:';

    /** @var array<string, list<string>> [orderingKey => [envelopeJson, ...]] */
    private array $queues = [];

    /** @var array<string, int> [orderingKey => priority] — sorted via arsort */
    private array $readyKeys = [];

    /** @var array<string, int> [deliveryId => availableAt] */
    private array $delayed = [];

    /** @var array<string, string> [deliveryId => envelopeJson] */
    private array $delayedData = [];

    /** @var array<string, int> [envelopeJson => availableAt] */
    private array $globalDelayed = [];

    /** @var array<string, array{orderingKey: string, envelopeJson: string, leaseExpiry: int}> */
    private array $inflight = [];

    /** @var array<string, int> [envelopeJson => priority] — broadcast, sorted via arsort */
    private array $global = [];

    /** @var array<string, array<string, string>> [channel => [entryId => entryJson]] */
    private array $dlq = [];

    private int $seq = 0;

    public function __construct(
        private readonly ASKClockContract $clock,
        private readonly int $maxSize = 10000,
    ) {
    }

    public static function build(
        ASKClockContract $clock,
        ?string $dsn = null,
        int $maxSize = 10000,
        bool $useLuaOptimization = true,
    ): self {
        return new self($clock, $maxSize);
    }

    public function push(OutboundTask $task): void
    {
        $this->guardCapacity();

        $envelope = new OutboundEnvelope($task, new OutboundTaskState());
        $envelopeJson = json_encode($envelope, JSON_THROW_ON_ERROR);
        $priority = $task->priority->value;
        $orderingKey = $task->orderingKey;

        if ($orderingKey !== null && $orderingKey !== '') {
            $this->queues[$orderingKey][] = $envelopeJson;
            if (count($this->queues[$orderingKey]) === 1) {
                $this->readyKeys[$orderingKey] = $priority;
                $this->sortReadyKeys();
            }
        } else {
            $this->global[$envelopeJson] = $priority;
            $this->sortGlobal();
        }
    }

    public function pop(int $visibilityTimeoutSec = 60): ?OutboundEnvelope
    {
        $now = $this->clock->time();
        $leaseExpiry = $now + max(1, $visibilityTimeoutSec);

        $this->reclaimExpired();
        $this->hydrateDelayed();

        $deliveryId = (string) (++$this->seq);

        $orderingKey = $this->lockNextReadyKey();

        if ($orderingKey !== null) {
            $envelopeJson = array_shift($this->queues[$orderingKey]);
            if ($this->queues[$orderingKey] === []) {
                unset($this->queues[$orderingKey]);
            }

            if ($envelopeJson === null) {
                return null;
            }

            $this->inflight[$deliveryId] = [
                'orderingKey' => $orderingKey,
                'envelopeJson' => $envelopeJson,
                'leaseExpiry' => $leaseExpiry,
            ];

            $envelope = OutboundEnvelope::fromJson(json_decode($envelopeJson, true));
            $envelope->deliveryId = $deliveryId;

            return $envelope;
        }

        $globalMin = $this->global !== [] ? array_key_first($this->global) : null;
        if ($globalMin !== null) {
            $envelopeJson = (string) $globalMin;
            unset($this->global[$envelopeJson]);

            $this->inflight[$deliveryId] = [
                'orderingKey' => '',
                'envelopeJson' => $envelopeJson,
                'leaseExpiry' => $leaseExpiry,
            ];

            $envelope = OutboundEnvelope::fromJson(json_decode($envelopeJson, true));
            $envelope->deliveryId = $deliveryId;

            return $envelope;
        }

        return null;
    }

    public function ack(OutboundEnvelope $envelope): void
    {
        $deliveryId = $envelope->deliveryId;
        if ($deliveryId === null) {
            return;
        }

        if (! isset($this->inflight[$deliveryId])) {
            return;
        }

        $data = $this->inflight[$deliveryId];
        unset($this->inflight[$deliveryId]);

        if (! empty($data['orderingKey'])) {
            $this->refreshKeyState($data['orderingKey']);
        }
    }

    public function release(OutboundEnvelope $envelope, int $delaySec): void
    {
        $deliveryId = $envelope->deliveryId;
        if ($deliveryId === null) {
            return;
        }

        if (! isset($this->inflight[$deliveryId])) {
            return;
        }

        $data = $this->inflight[$deliveryId];
        unset($this->inflight[$deliveryId]);

        if ($delaySec > 0) {
            if (! empty($data['orderingKey'])) {
                $this->delayedData[$deliveryId] = $data['envelopeJson'];
                $this->delayed[$deliveryId] = $this->clock->time() + $delaySec;
            } else {
                $this->globalDelayed[$data['envelopeJson']] = $this->clock->time() + $delaySec;
            }
        } else {
            if (! empty($data['orderingKey'])) {
                array_unshift($this->queues[$data['orderingKey']], $data['envelopeJson']);
                $this->refreshKeyState($data['orderingKey']);
            } else {
                $envelopeData = json_decode($data['envelopeJson'], true);
                $priority = $envelopeData['task']['priority']['value'] ?? 0;
                $this->global[$data['envelopeJson']] = $priority;
                $this->sortGlobal();
            }
        }
    }

    public function renewLease(OutboundEnvelope $envelope, int $seconds): bool
    {
        $deliveryId = $envelope->deliveryId;
        if ($deliveryId === null || ! isset($this->inflight[$deliveryId])) {
            return false;
        }
        $this->inflight[$deliveryId]['leaseExpiry'] = $this->clock->time() + max(1, $seconds);

        return true;
    }

    public function size(): int
    {
        return count($this->readyKeys) + count($this->global) + count($this->delayed) + count($this->globalDelayed);
    }

    // ----- AtomicDlqQueueContract -----

    public function pushToDeadLetter(OutboundEnvelope $envelope, string $reason): string
    {
        $entry = DeadLetterEntry::fromEnvelope($envelope, $reason);
        $channel = $this->dlqChannel($envelope->task->botConfig->botId);
        $this->dlq[$channel][$entry->id] = json_encode($entry, JSON_THROW_ON_ERROR);

        return $entry->id;
    }

    public function atomicFetchAndRemoveFromDlq(string $channel, string $entryId): ?string
    {
        if (! isset($this->dlq[$channel][$entryId])) {
            return null;
        }
        $json = $this->dlq[$channel][$entryId];
        unset($this->dlq[$channel][$entryId]);
        if ($this->dlq[$channel] === []) {
            unset($this->dlq[$channel]);
        }

        return $json;
    }

    public function listDeadLetter(?string $channel, int $limit = 50): array
    {
        $result = [];
        if ($channel !== null) {
            foreach (array_slice($this->dlq[$channel] ?? [], 0, $limit) as $entryJson) {
                $data = json_decode($entryJson, true);
                if (is_array($data)) {
                    $result[] = DeadLetterEntry::fromJson($data);
                }
            }

            return $result;
        }
        foreach ($this->dlq as $entries) {
            foreach ($entries as $entryJson) {
                $data = json_decode($entryJson, true);
                if (is_array($data)) {
                    $result[] = DeadLetterEntry::fromJson($data);
                }
                if (count($result) >= $limit) {
                    return $result;
                }
            }
        }

        return $result;
    }

    public function deadLetterSize(?string $channel = null): int
    {
        if ($channel !== null) {
            return count($this->dlq[$channel] ?? []);
        }
        $total = 0;
        foreach ($this->dlq as $entries) {
            $total += count($entries);
        }

        return $total;
    }

    // ----- ChannelDiscoverableQueueContract -----

    public function getDlqChannels(string $pattern): array
    {
        $channels = array_keys($this->dlq);
        $matched = [];
        foreach ($channels as $ch) {
            if (fnmatch($pattern, $ch)) {
                $matched[] = $ch;
            }
        }

        return $matched;
    }

    // ----- PurgeableQueueContract -----

    public function purgeExpired(string $channelPattern, int $beforeTimestamp): int
    {
        $purged = 0;
        foreach ($this->dlq as $channel => $entries) {
            if (! fnmatch($channelPattern, $channel)) {
                continue;
            }
            foreach ($entries as $entryId => $entryJson) {
                $data = json_decode($entryJson, true);
                if (! is_array($data) || ! isset($data['failedAt'])) {
                    continue;
                }
                $failedAtTs = (new \DateTimeImmutable((string) $data['failedAt']))->getTimestamp();
                if ($failedAtTs < $beforeTimestamp) {
                    unset($this->dlq[$channel][$entryId]);
                    $purged++;
                }
            }
            if ($this->dlq[$channel] === []) {
                unset($this->dlq[$channel]);
            }
        }

        return $purged;
    }

    // ----- helpers -----

    private function sortReadyKeys(): void
    {
        arsort($this->readyKeys, SORT_NUMERIC);
    }

    private function sortGlobal(): void
    {
        arsort($this->global, SORT_NUMERIC);
    }

    private function dlqChannel(string $botId): string
    {
        return self::DLQ_PREFIX.$botId;
    }

    private function guardCapacity(): void
    {
        $current = $this->size();
        if ($current >= $this->maxSize) {
            throw new OutboundBackpressureException(self::QUEUE_NAME, $current, $this->maxSize);
        }
    }

    // ----- OutboundOrderingQueueContract -----

    public function lockNextReadyKey(): ?string
    {
        if ($this->readyKeys === []) {
            return null;
        }

        reset($this->readyKeys);
        $orderingKey = (string) key($this->readyKeys);
        unset($this->readyKeys[$orderingKey]);

        return $orderingKey;
    }

    public function refreshKeyState(string $orderingKey): void
    {
        if (! isset($this->queues[$orderingKey]) || $this->queues[$orderingKey] === []) {
            return;
        }

        $nextTaskJson = $this->queues[$orderingKey][0];
        $nextTask = json_decode($nextTaskJson, true);
        $priority = (isset($nextTask['task']['priority']['value']))
            ? (int) $nextTask['task']['priority']['value']
            : 0;

        $this->readyKeys[$orderingKey] = $priority;
        $this->sortReadyKeys();
    }

    public function hydrateDelayed(): int
    {
        $now = $this->clock->time();
        $moved = 0;

        foreach ($this->delayed as $deliveryId => $availableAt) {
            if ($availableAt > $now) {
                continue;
            }

            $envelopeJson = $this->delayedData[$deliveryId] ?? null;
            if ($envelopeJson === null) {
                unset($this->delayed[$deliveryId]);

                continue;
            }

            $envelopeData = json_decode($envelopeJson, true);
            $orderingKey = $envelopeData['task']['orderingKey'] ?? null;
            $priority = $envelopeData['task']['priority']['value'] ?? 0;

            if ($orderingKey !== null && $orderingKey !== '') {
                $this->queues[$orderingKey][] = $envelopeJson;
                $this->refreshKeyState($orderingKey);
            } else {
                $this->global[$envelopeJson] = $priority;
                $this->sortGlobal();
            }

            unset($this->delayedData[$deliveryId]);
            unset($this->delayed[$deliveryId]);
            $moved++;
        }

        foreach ($this->globalDelayed as $envelopeJson => $availableAt) {
            if ($availableAt > $now) {
                continue;
            }

            $envelopeData = json_decode($envelopeJson, true);
            $priority = $envelopeData['task']['priority']['value'] ?? 0;
            $this->global[$envelopeJson] = $priority;
            $this->sortGlobal();
            unset($this->globalDelayed[$envelopeJson]);
            $moved++;
        }

        return $moved;
    }

    public function reclaimExpired(): int
    {
        $now = $this->clock->time();
        $reclaimed = 0;

        foreach ($this->inflight as $deliveryId => $data) {
            if ($data['leaseExpiry'] >= $now) {
                continue;
            }

            if (! empty($data['orderingKey'])) {
                $this->queues[$data['orderingKey']][] = $data['envelopeJson'];
                unset($this->inflight[$deliveryId]);
                $this->refreshKeyState($data['orderingKey']);
            } else {
                $envelopeData = json_decode($data['envelopeJson'], true);
                $priority = $envelopeData['task']['priority']['value'] ?? 0;
                $this->global[$data['envelopeJson']] = $priority;
                $this->sortGlobal();
                unset($this->inflight[$deliveryId]);
            }
            $reclaimed++;
        }

        return $reclaimed;
    }
}
