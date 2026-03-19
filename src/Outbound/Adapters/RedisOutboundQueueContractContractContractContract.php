<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound\Adapters;

use BAGArt\ASKClientRedis\Redis\Contract\RedisClientContract;
use BAGArt\AsyncKernel\Contracts\ASKClockContract;
use BAGArt\TelegramBot\Contracts\Outbound\AtomicDlqQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\ChannelDiscoverableQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\LeaseRenewableQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundOrderingQueueContract;
use BAGArt\TelegramBot\Contracts\Outbound\PurgeableQueueContract;
use BAGArt\TelegramBot\Outbound\DeadLetterEntry;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\OutboundTaskState;

final class RedisOutboundQueueContractContractContractContract implements AtomicDlqQueueContract, ChannelDiscoverableQueueContract, LeaseRenewableQueueContract, OutboundOrderingQueueContract, PurgeableQueueContract
{
    public const string TYPE = 'redis';

    private const string READY_KEYS = 'tg_outbound:ready_keys';

    private const string QUEUE_PREFIX = 'tg_outbound:q:';

    private const string DELAYED_KEY = 'tg_outbound:delayed';

    private const string DELAYED_DATA_PREFIX = 'tg_outbound:delayed:data:';

    private const string INFLIGHT_KEY = 'tg_outbound:inflight';

    private const string INFLIGHT_SEQ_KEY = 'tg_outbound:inflight:seq';

    private const string GLOBAL_KEY = 'tg_outbound:global';

    private const string GLOBAL_DELAYED_KEY = 'tg_outbound:global:delayed';

    private const string DLQ_PREFIX = 'tg-dlq:';

    private const string LUA_PUSH = <<<'LUA'
local readyKeys = KEYS[1]
local qKey = KEYS[2]
local orderingKey = ARGV[1]
local taskJson = ARGV[2]
local priority = tonumber(ARGV[3])

redis.call('RPUSH', qKey, taskJson)
if redis.call('LLEN', qKey) == 1 then
    redis.call('ZADD', readyKeys, priority, orderingKey)
end
LUA;

    private const string LUA_POP = <<<'LUA'
local readyKeys = KEYS[1]
local inflight = KEYS[2]
local seqKey = KEYS[3]
local globalKey = KEYS[4]
local now = tonumber(ARGV[1])
local leaseExpiry = tonumber(ARGV[2])

local ready = redis.call('ZPOPMAX', readyKeys)
if ready[1] then
    local orderingKey = ready[1]
    local qKey = 'tg_outbound:q:' .. orderingKey
    local taskJson = redis.call('LPOP', qKey)

    if taskJson then
        local deliveryId = redis.call('HINCRBY', seqKey, 'seq', 1)
        local inflightEntry = cjson.encode({
            orderingKey = orderingKey,
            envelopeJson = taskJson,
            leaseExpiry = leaseExpiry
        })
        redis.call('HSET', inflight, deliveryId, inflightEntry)
        return cjson.encode({deliveryId = tostring(deliveryId), envelope = taskJson})
    end
end

local globalPopped = redis.call('ZPOPMAX', globalKey)
if globalPopped[1] then
    local taskJson = globalPopped[1]
    local deliveryId = redis.call('HINCRBY', seqKey, 'seq', 1)
    local inflightEntry = cjson.encode({
        orderingKey = '',
        envelopeJson = taskJson,
        leaseExpiry = leaseExpiry
    })
    redis.call('HSET', inflight, deliveryId, inflightEntry)
    return cjson.encode({deliveryId = tostring(deliveryId), envelope = taskJson})
end

return nil
LUA;

    private const string LUA_ACK = <<<'LUA'
local readyKeys = KEYS[1]
local inflight = KEYS[2]
local deliveryId = ARGV[1]

local inflightJson = redis.call('HGET', inflight, deliveryId)
if not inflightJson then return 0 end

local data = cjson.decode(inflightJson)
redis.call('HDEL', inflight, deliveryId)

if data.orderingKey and data.orderingKey ~= '' then
    local qKey = 'tg_outbound:q:' .. data.orderingKey
    local nextTaskJson = redis.call('LINDEX', qKey, 0)
    if nextTaskJson then
        local nextTask = cjson.decode(nextTaskJson)
        local p = nextTask.task and nextTask.task.priority
        local priority = (type(p) == 'number' and p) or (type(p) == 'table' and p.value) or 0
        redis.call('ZADD', readyKeys, priority, data.orderingKey)
    end
end
return 1
LUA;

    private const string LUA_RELEASE = <<<'LUA'
local readyKeys = KEYS[1]
local inflight = KEYS[2]
local delayed = KEYS[3]
local globalKey = KEYS[4]
local globalDelayed = KEYS[5]
local deliveryId = ARGV[1]
local delaySec = tonumber(ARGV[2])
local now = tonumber(ARGV[3])

local inflightJson = redis.call('HGET', inflight, deliveryId)
if not inflightJson then return 0 end

local data = cjson.decode(inflightJson)
redis.call('HDEL', inflight, deliveryId)

if delaySec > 0 then
    if data.orderingKey and data.orderingKey ~= '' then
        redis.call('SET', 'tg_outbound:delayed:data:' .. deliveryId, data.envelopeJson)
        redis.call('ZADD', delayed, now + delaySec, deliveryId)
    else
        local task = cjson.decode(data.envelopeJson)
        local p = task.task and task.task.priority
        local priority = (type(p) == 'number' and p) or (type(p) == 'table' and p.value) or 0
        redis.call('ZADD', globalDelayed, now + delaySec, data.envelopeJson)
    end
else
    if data.orderingKey and data.orderingKey ~= '' then
        local qKey = 'tg_outbound:q:' .. data.orderingKey
        redis.call('LPUSH', qKey, data.envelopeJson)

        local task = cjson.decode(data.envelopeJson)
        local p = task.task and task.task.priority
        local priority = (type(p) == 'number' and p) or (type(p) == 'table' and p.value) or 0
        redis.call('ZADD', readyKeys, priority, data.orderingKey)
    else
        local task = cjson.decode(data.envelopeJson)
        local p = task.task and task.task.priority
        local priority = (type(p) == 'number' and p) or (type(p) == 'table' and p.value) or 0
        redis.call('ZADD', globalKey, priority, data.envelopeJson)
    end
end
return 1
LUA;

    private const string LUA_RENEW = <<<'LUA'
local inflight = KEYS[1]
local deliveryId = ARGV[1]
local newExpiry = ARGV[2]
local payload = redis.call("HGET", inflight, deliveryId)
if not payload then return 0 end
local ok, data = pcall(cjson.decode, payload)
if not ok or not data then return 0 end
data.leaseExpiry = tonumber(newExpiry)
redis.call("HSET", inflight, deliveryId, cjson.encode(data))
return 1
LUA;

    private const string LUA_DLQ_FETCH = <<<'LUA'
local channel = KEYS[1]
local entryId = ARGV[1]
local payload = redis.call("HGET", channel, entryId)
if not payload then return nil end
redis.call("HDEL", channel, entryId)
return payload
LUA;

    public function __construct(
        private readonly RedisClientContract $redis,
        private readonly ASKClockContract $clock,
        private readonly bool $useLuaOptimization = true,
    ) {
    }

    public function push(OutboundTask $task): void
    {
        $envelope = new OutboundEnvelope($task, new OutboundTaskState());
        $envelopeJson = json_encode($envelope, JSON_THROW_ON_ERROR);
        $priority = $task->priority->value;
        $orderingKey = $task->orderingKey;

        if ($orderingKey !== null && $orderingKey !== '') {
            if ($this->useLuaOptimization) {
                $this->redis->eval(self::LUA_PUSH, [
                    self::READY_KEYS, self::QUEUE_PREFIX.$orderingKey,
                    $orderingKey, $envelopeJson, (string) $priority,
                ], 2);
            } else {
                $qKey = self::QUEUE_PREFIX.$orderingKey;
                $this->redis->rPush($qKey, $envelopeJson);
                if ($this->redis->lLen($qKey) === 1) {
                    $this->redis->zAdd(self::READY_KEYS, $priority, $orderingKey);
                }
            }
        } else {
            $this->redis->zAdd(self::GLOBAL_KEY, $priority, $envelopeJson);
        }
    }

    public function pop(int $visibilityTimeoutSec = 60): ?OutboundEnvelope
    {
        $now = $this->clock->time();
        $leaseExpiry = $now + max(1, $visibilityTimeoutSec);

        if ($this->useLuaOptimization) {
            $result = $this->redis->eval(self::LUA_POP, [
                self::READY_KEYS, self::INFLIGHT_KEY, self::INFLIGHT_SEQ_KEY, self::GLOBAL_KEY,
                (string) $now, (string) $leaseExpiry,
            ], 4);

            if ($result === false || $result === null) {
                return null;
            }

            $decoded = json_decode((string) $result, true);
        } else {
            $decoded = $this->popPhpNative($now, $leaseExpiry);
        }

        if (! $decoded) {
            return null;
        }

        $envelopeData = is_string($decoded['envelope'])
            ? json_decode($decoded['envelope'], true)
            : $decoded['envelope'];

        $envelope = OutboundEnvelope::fromJson((array) $envelopeData);
        $envelope->deliveryId = (string) $decoded['deliveryId'];

        return $envelope;
    }

    public function ack(OutboundEnvelope $envelope): void
    {
        $deliveryId = $envelope->deliveryId;
        if ($deliveryId === null) {
            return;
        }

        if ($this->useLuaOptimization) {
            $this->redis->eval(self::LUA_ACK, [self::READY_KEYS, self::INFLIGHT_KEY, $deliveryId], 2);

            return;
        }

        $inflightJson = $this->redis->hGet(self::INFLIGHT_KEY, $deliveryId);
        if ($inflightJson === false || $inflightJson === null) {
            return;
        }

        $data = json_decode($inflightJson, true);
        $this->redis->hDel(self::INFLIGHT_KEY, $deliveryId);

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

        if ($this->useLuaOptimization) {
            $this->redis->eval(self::LUA_RELEASE, [
                self::READY_KEYS, self::INFLIGHT_KEY, self::DELAYED_KEY, self::GLOBAL_KEY, self::GLOBAL_DELAYED_KEY,
                $deliveryId, (string) $delaySec, (string) $this->clock->time(),
            ], 5);

            return;
        }

        $inflightJson = $this->redis->hGet(self::INFLIGHT_KEY, $deliveryId);
        if ($inflightJson === false || $inflightJson === null) {
            return;
        }

        $data = json_decode($inflightJson, true);
        $this->redis->hDel(self::INFLIGHT_KEY, $deliveryId);

        if ($delaySec > 0) {
            if (! empty($data['orderingKey'])) {
                $this->redis->set(self::DELAYED_DATA_PREFIX.$deliveryId, $data['envelopeJson']);
                $this->redis->zAdd(self::DELAYED_KEY, $this->clock->time() + $delaySec, $deliveryId);
            } else {
                $envelopeData = json_decode($data['envelopeJson'], true);
                $priority = $envelopeData['task']['priority']['value'] ?? 0;
                $this->redis->zAdd(self::GLOBAL_DELAYED_KEY, $this->clock->time() + $delaySec, $data['envelopeJson']);
            }
        } else {
            if (! empty($data['orderingKey'])) {
                $this->redis->lPush(self::QUEUE_PREFIX.$data['orderingKey'], $data['envelopeJson']);
                $this->refreshKeyState($data['orderingKey']);
            } else {
                $envelopeData = json_decode($data['envelopeJson'], true);
                $priority = $envelopeData['task']['priority']['value'] ?? 0;
                $this->redis->zAdd(self::GLOBAL_KEY, $priority, $data['envelopeJson']);
            }
        }
    }

    public function lockNextReadyKey(): ?string
    {
        $ready = $this->redis->zPopMax(self::READY_KEYS);
        if (empty($ready)) {
            return null;
        }

        return (string) array_key_first($ready);
    }

    public function refreshKeyState(string $orderingKey): void
    {
        $qKey = self::QUEUE_PREFIX.$orderingKey;
        $nextTaskJson = $this->redis->lIndex($qKey, 0);

        if ($nextTaskJson !== false && $nextTaskJson !== null) {
            $nextTask = json_decode($nextTaskJson, true);
            $priority = (isset($nextTask['task']['priority']['value']))
                ? (int) $nextTask['task']['priority']['value']
                : 0;

            $this->redis->zAdd(self::READY_KEYS, $priority, $orderingKey);
        }
    }

    public function hydrateDelayed(): int
    {
        $now = $this->clock->time();
        $moved = 0;

        $readyIds = $this->redis->zRangeByScore(
            self::DELAYED_KEY,
            '0',
            (string) $now,
            ['limit' => [0, 100]]
        );
        if (! is_array($readyIds)) {
            return 0;
        }

        foreach ($readyIds as $deliveryId) {
            $dataKey = self::DELAYED_DATA_PREFIX.$deliveryId;
            $envelopeJson = $this->redis->get($dataKey);

            if ($envelopeJson === false || $envelopeJson === null) {
                $this->redis->zRem(self::DELAYED_KEY, $deliveryId);

                continue;
            }

            $envelopeData = json_decode($envelopeJson, true);
            $orderingKey = $envelopeData['task']['orderingKey'] ?? null;
            $priority = $envelopeData['task']['priority']['value'] ?? 0;

            if ($orderingKey !== null && $orderingKey !== '') {
                $qKey = self::QUEUE_PREFIX.$orderingKey;
                $this->redis->rPush($qKey, $envelopeJson);
                if ($this->redis->lLen($qKey) === 1) {
                    $this->redis->zAdd(self::READY_KEYS, $priority, $orderingKey);
                }
            } else {
                $this->redis->zAdd(self::GLOBAL_KEY, $priority, $envelopeJson);
            }

            $this->redis->del($dataKey);
            $this->redis->zRem(self::DELAYED_KEY, $deliveryId);
            $moved++;
        }

        $readyGlobal = $this->redis->zRangeByScore(
            self::GLOBAL_DELAYED_KEY,
            '0',
            (string) $now,
            ['limit' => [0, 100]]
        );
        if (is_array($readyGlobal)) {
            foreach ($readyGlobal as $envelopeJson) {
                $envelopeData = json_decode($envelopeJson, true);
                $priority = $envelopeData['task']['priority']['value'] ?? 0;
                $this->redis->zAdd(self::GLOBAL_KEY, $priority, $envelopeJson);
                $this->redis->zRem(self::GLOBAL_DELAYED_KEY, $envelopeJson);
                $moved++;
            }
        }

        return $moved;
    }

    public function reclaimExpired(): int
    {
        $now = $this->clock->time();
        $reclaimed = 0;
        $cursor = null;

        do {
            $results = $this->redis->hScan(self::INFLIGHT_KEY, $cursor, '*', 100);
            if ($results === false) {
                break;
            }

            foreach ($results as $deliveryId => $inflightJson) {
                $data = json_decode($inflightJson, true);
                if ((int) $data['leaseExpiry'] >= $now) {
                    continue;
                }

                if (! empty($data['orderingKey'])) {
                    $this->redis->lPush(self::QUEUE_PREFIX.$data['orderingKey'], $data['envelopeJson']);
                    $this->redis->hDel(self::INFLIGHT_KEY, $deliveryId);
                    $this->refreshKeyState($data['orderingKey']);
                } else {
                    $envelopeData = json_decode($data['envelopeJson'], true);
                    $priority = $envelopeData['task']['priority']['value'] ?? 0;
                    $this->redis->zAdd(self::GLOBAL_KEY, $priority, $data['envelopeJson']);
                    $this->redis->hDel(self::INFLIGHT_KEY, $deliveryId);
                }
                $reclaimed++;
            }
        } while ($cursor > 0);

        return $reclaimed;
    }

    public function renewLease(OutboundEnvelope $envelope, int $seconds): bool
    {
        $deliveryId = $envelope->deliveryId;
        if ($deliveryId === null) {
            return false;
        }

        $newExpiry = $this->clock->time() + max(1, $seconds);
        $result = $this->redis->eval(
            self::LUA_RENEW,
            [self::INFLIGHT_KEY, $deliveryId, (string) $newExpiry],
            1,
        );

        return (bool) $result;
    }

    public function size(): int
    {
        return (int) $this->redis->zCard(self::READY_KEYS)
            + (int) $this->redis->zCard(self::GLOBAL_KEY)
            + (int) $this->redis->zCard(self::DELAYED_KEY)
            + (int) $this->redis->zCard(self::GLOBAL_DELAYED_KEY);
    }

    // ----- AtomicDlqQueueContract -----

    public function pushToDeadLetter(OutboundEnvelope $envelope, string $reason): string
    {
        $entry = DeadLetterEntry::fromEnvelope($envelope, $reason);
        $channel = $this->dlqChannel($envelope->task->botConfig->botId);
        $this->redis->hSet($channel, $entry->id, json_encode($entry, JSON_THROW_ON_ERROR));

        return $entry->id;
    }

    public function atomicFetchAndRemoveFromDlq(string $channel, string $entryId): ?string
    {
        $result = $this->redis->eval(self::LUA_DLQ_FETCH, [$channel, $entryId], 1);
        if ($result === false || $result === null) {
            return null;
        }

        return (string) $result;
    }

    public function listDeadLetter(?string $channel, int $limit = 50): array
    {
        $result = [];
        $channels = $channel !== null ? [$channel] : $this->getDlqChannels(self::DLQ_PREFIX.'*');

        foreach ($channels as $ch) {
            $raw = $this->redis->hGetAll($ch);
            if (! is_array($raw) || $raw === []) {
                continue;
            }
            foreach ($raw as $entryJson) {
                $data = json_decode((string) $entryJson, true);
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
            return (int) $this->redis->hLen($channel);
        }
        $total = 0;
        foreach ($this->getDlqChannels(self::DLQ_PREFIX.'*') as $ch) {
            $total += (int) $this->redis->hLen($ch);
        }

        return $total;
    }

    // ----- ChannelDiscoverableQueueContract -----

    public function getDlqChannels(string $pattern): array
    {
        $matched = [];
        $iterator = null;
        while ($keys = $this->redis->scan($iterator, $pattern)) {
            foreach ($keys as $key) {
                $matched[] = $key;
            }
        }

        return $matched;
    }

    // ----- PurgeableQueueContract -----

    public function purgeExpired(string $channelPattern, int $beforeTimestamp): int
    {
        $purged = 0;
        foreach ($this->getDlqChannels($channelPattern) as $channel) {
            $raw = $this->redis->hGetAll($channel);
            if (! is_array($raw)) {
                continue;
            }
            foreach ($raw as $entryId => $entryJson) {
                $data = json_decode((string) $entryJson, true);
                if (! is_array($data) || ! isset($data['failedAt'])) {
                    continue;
                }
                $failedAtTs = (new \DateTimeImmutable((string) $data['failedAt']))->getTimestamp();
                if ($failedAtTs < $beforeTimestamp) {
                    $this->redis->hDel($channel, (string) $entryId);
                    $purged++;
                }
            }
        }

        return $purged;
    }

    // ----- helpers -----

    private function popPhpNative(int $now, int $leaseExpiry): ?array
    {
        $deliveryId = (string) $this->redis->hIncrBy(self::INFLIGHT_SEQ_KEY, 'seq', 1);
        $orderingKey = $this->lockNextReadyKey();

        if ($orderingKey !== null) {
            $qKey = self::QUEUE_PREFIX.$orderingKey;
            $taskJson = $this->redis->lPop($qKey);
            if ($taskJson === false || $taskJson === null) {
                return null;
            }

            $this->redis->hSet(self::INFLIGHT_KEY, $deliveryId, json_encode([
                'orderingKey' => $orderingKey,
                'envelopeJson' => $taskJson,
                'leaseExpiry' => $leaseExpiry,
            ], JSON_THROW_ON_ERROR));

            return ['deliveryId' => $deliveryId, 'envelope' => $taskJson];
        }

        $global = $this->redis->zPopMax(self::GLOBAL_KEY);
        if (! empty($global)) {
            $taskJson = (string) array_key_first($global);
            $this->redis->hSet(self::INFLIGHT_KEY, $deliveryId, json_encode([
                'orderingKey' => '',
                'envelopeJson' => $taskJson,
                'leaseExpiry' => $leaseExpiry,
            ], JSON_THROW_ON_ERROR));

            return ['deliveryId' => $deliveryId, 'envelope' => $taskJson];
        }

        return null;
    }

    private function dlqChannel(string $botId): string
    {
        return self::DLQ_PREFIX.$botId;
    }
}
