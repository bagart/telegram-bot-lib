<?php

declare(strict_types=1);

use BAGArt\ASKClientRedis\Redis\RedisDsn;
use BAGArt\AsyncKernel\ASKClock;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Outbound\OutboundQueueRegistry;

require_once __DIR__.'/../../../../vendor/autoload.php';

$allowedOptions = [
    'status',
    'workers',
    'ready-keys',
    'bot-id::',
    'chat-id::',
    'trace-task::',
    'peek',
    'limit::',
    'delayed',
    'bottlenecks',
    'redis-host::',
    'redis-port::',
    'redis-timeout::',
    'json',
    'mode::',
    'help',
];

$options = CommandActions::parseOptions(
    getopt('', $allowedOptions),
    $allowedOptions,
);

if (isset($options['help'])) {
    echo 'Usage:
php commands/outbound-tool.php [options]

Options:
  --status                 Show queue sizes (ready/delayed/inflight/dlq)
  --workers                Show live workers (heartbeat), stale cleanup
  --ready-keys             Show current ready keys with priority and queue sizes
  --bot-id={id}            Bot ID for trace-task
  --chat-id={id}           Chat ID for trace-task
  --trace-task={id}        Search task by ID across ready/delayed/inflight/dlq
  --peek                   Peek at queue tasks without popping (use --limit=N)
  --limit=N                Max results (default: 50)
  --delayed                Show delayed tasks in retry
  --bottlenecks            Show top retry reasons
  --json                   Structured JSON output
  --mode=single|multi      Queue mode (default: single)
  --redis-host=127.0.0.1   Redis host (for --mode=multi)
  --redis-port=6379        Redis port
  --redis-timeout=2.0      Redis connection timeout
  --help
';
    exit(0);
}

$clock = new ASKClock();
$mode = $options['mode'] ?? 'single';
$jsonOutput = isset($options['json']);
$limit = (int)($options['limit'] ?? 50);

function out(mixed $data, bool $json): void
{
    if ($json) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    } else {
        echo (string) $data;
    }
}

if ($mode === 'multi') {
    $dsn = new RedisDsn(
        host: (string)($options['redis-host'] ?? '127.0.0.1'),
        port: (int)($options['redis-port'] ?? 6379),
        timeout: (float)($options['redis-timeout'] ?? 2.0),
    );
    $redis = $dsn->connect();
    $queue = OutboundQueueRegistry::build()->make(type: 'redis', clock: $clock, dsn: $dsn->toString());
}

// ---- Status ----
if (isset($options['status'])) {
    if ($mode === 'multi') {
        $ready = (int)$redis->zCard('tg_outbound:ready_keys');
        $delayed = (int)$redis->zCard('tg_outbound:delayed')
            + (int)$redis->zCard('tg_outbound:global:delayed');
        $inflight = (int)$redis->hLen('tg_outbound:inflight');
        $dlqChannels = $queue->getDlqChannels('tg-dlq:*');
        $dlqTotal = 0;
        foreach ($dlqChannels as $ch) {
            $dlqTotal += (int)$redis->hLen($ch);
        }

        $result = [
            'ready' => $ready,
            'delayed' => $delayed,
            'inflight' => $inflight,
            'dlq' => ['total' => $dlqTotal, 'channels' => count($dlqChannels)],
            'total' => $ready + $delayed + $inflight + $dlqTotal,
        ];
    } else {
        $result = ['mode' => 'single', 'note' => 'Use --mode=multi with Redis for full status'];
    }

    out($result, $jsonOutput);
    exit(0);
}

// ---- Workers ----
if (isset($options['workers'])) {
    if ($mode !== 'multi') {
        out("--workers requires --mode=multi with Redis\n", $jsonOutput);
        exit(1);
    }

    $workers = $redis->hGetAll('tg_outbound:workers');
    $now = time();
    $alive = [];
    $stale = [];

    if (!is_array($workers)) {
        out(['error' => 'No worker heartbeat data'], $jsonOutput);
        exit(0);
    }

    foreach ($workers as $workerId => $payload) {
        $data = json_decode((string)$payload, true);
        if (!is_array($data)) {
            continue;
        }
        $age = $now - ($data['updated_at'] ?? 0);
        if ($age > 30) {
            $stale[] = array_merge(['id' => $workerId, 'age_sec' => $age], $data);
        } else {
            $alive[] = array_merge(['id' => $workerId, 'age_sec' => $age], $data);
        }
    }

    $result = ['alive' => $alive, 'stale' => $stale];

    if (!$jsonOutput) {
        echo "=== Workers ===\n";
        echo "Alive (" . count($alive) . "):\n";
        foreach ($alive as $w) {
            $processed = $w['processed'] ?? 0;
            echo "  {$w['id']}: processed={$processed}";
            if (isset($w['current_task_id'])) {
                echo " task={$w['current_task_id']}";
            }
            echo "\n";
        }
        if ($stale !== []) {
            echo "Stale (" . count($stale) . "):\n";
            foreach ($stale as $w) {
                echo "  {$w['id']}: age={$w['age_sec']}s\n";
            }
        }
    } else {
        out($result, true);
    }
    exit(0);
}

if ($mode !== 'multi' && !isset($options['status'])) {
    out("All inspection commands require --mode=multi with Redis\n", $jsonOutput);
    exit(1);
}

// ---- Ready Keys ----
if (isset($options['ready-keys'])) {
    $readyKeys = $redis->zRange('tg_outbound:ready_keys', 0, -1, true);

    if (!$jsonOutput) {
        echo "=== Ready Keys (" . count($readyKeys) . ") ===\n";
        foreach ($readyKeys as $orderingKey => $priority) {
            $qSize = (int)$redis->lLen('tg_outbound:q:' . $orderingKey);
            echo "  {$orderingKey}: priority={$priority}, queue_size={$qSize}\n";
        }
        if ($readyKeys === []) {
            echo "  (none)\n";
        }
    } else {
        $items = [];
        foreach ($readyKeys as $orderingKey => $priority) {
            $items[] = [
                'ordering_key' => $orderingKey,
                'priority' => $priority,
                'queue_size' => (int)$redis->lLen('tg_outbound:q:' . $orderingKey),
            ];
        }
        out($items, true);
    }
    exit(0);
}

// ---- Trace Task ----
if (isset($options['trace-task'])) {
    $taskId = $options['trace-task'];
    $found = [];

    // Check ready (iterate ready_keys, then scan per-key queues)
    $readyKeys = $redis->zRange('tg_outbound:ready_keys', 0, -1);
    foreach ($readyKeys as $orderingKey) {
        $qKey = 'tg_outbound:q:' . $orderingKey;
        $tasks = $redis->lRange($qKey, 0, -1);
        if (!is_array($tasks)) {
            continue;
        }
        foreach ($tasks as $envelopeJson) {
            $data = json_decode((string)$envelopeJson, true);
            if (is_array($data) && ($data['task']['id'] ?? null) === $taskId) {
                $found[] = ['location' => "ready:{$orderingKey}", 'envelope' => $data];
            }
        }
    }

    // Check delayed (members are deliveryIds, data in delayed:data:{id})
    $delayedIds = $redis->zRange('tg_outbound:delayed', 0, -1);
    foreach ($delayedIds as $deliveryId) {
        $envelopeJson = $redis->get('tg_outbound:delayed:data:' . $deliveryId);
        if ($envelopeJson === false || $envelopeJson === null) {
            continue;
        }
        $data = json_decode((string)$envelopeJson, true);
        if (is_array($data) && ($data['task']['id'] ?? null) === $taskId) {
            $found[] = ['location' => "delayed:{$deliveryId}", 'envelope' => $data];
        }
    }

    // Check global delayed (members are envelopeJson, score = availableAt)
    $globalDelayed = $redis->zRange('tg_outbound:global:delayed', 0, -1);
    foreach ($globalDelayed as $envelopeJson) {
        $data = json_decode((string)$envelopeJson, true);
        if (is_array($data) && ($data['task']['id'] ?? null) === $taskId) {
            $found[] = ['location' => 'global:delayed', 'envelope' => $data];
        }
    }

    // Check inflight
    $inflightData = $redis->hGetAll('tg_outbound:inflight');
    if (is_array($inflightData)) {
        foreach ($inflightData as $deliveryId => $entryJson) {
            $entry = json_decode((string)$entryJson, true);
            $envelope = is_array($entry) ? ($entry['envelope'] ?? null) : null;
            if (is_string($envelope)) {
                $envelope = json_decode($envelope, true);
            }
            if (is_array($envelope) && ($envelope['task']['id'] ?? null) === $taskId) {
                $found[] = ['location' => "inflight:{$deliveryId}", 'envelope' => $envelope];
            }
        }
    }

    // Check DLQ
    $dlqChannels = $queue->getDlqChannels('tg-dlq:*');
    foreach ($dlqChannels as $channel) {
        $entries = $redis->hGetAll($channel);
        if (!is_array($entries)) {
            continue;
        }
        foreach ($entries as $entryId => $entryJson) {
            $data = json_decode((string)$entryJson, true);
            if (is_array($data) && ($data['id'] ?? null) === $taskId) {
                $found[] = ['location' => "dlq:{$channel}:{$entryId}", 'dead_letter' => $data];
            }
        }
    }

    if (!$jsonOutput) {
        if ($found === []) {
            echo "Task {$taskId} not found\n";
        } else {
            echo "Task {$taskId} found in " . count($found) . " location(s):\n";
            foreach ($found as $f) {
                echo "  {$f['location']}\n";
            }
        }
    } else {
        out(['task_id' => $taskId, 'found' => $found === [] ? null : $found], true);
    }
    exit(0);
}

// ---- Peek ----
if (isset($options['peek'])) {
    $tasks = [];

    $readyKeys = $redis->zRange('tg_outbound:ready_keys', 0, -1);
    foreach ($readyKeys as $orderingKey) {
        if (count($tasks) >= $limit) {
            break;
        }
        $qKey = 'tg_outbound:q:' . $orderingKey;
        $queueTasks = $redis->lRange($qKey, 0, $limit - count($tasks) - 1);
        if (!is_array($queueTasks)) {
            continue;
        }
        foreach ($queueTasks as $envelopeJson) {
            $data = json_decode((string)$envelopeJson, true);
            if (is_array($data)) {
                $data['_source'] = 'ready';
                $data['_orderingKey'] = $orderingKey;
                $tasks[] = $data;
            }
        }
    }

    if (!$jsonOutput) {
        echo "=== Queue Peek (ready, limit={$limit}) ===\n";
        foreach ($tasks as $i => $t) {
            $task = $t['task'] ?? [];
            echo ($i + 1) . ". {$task['dtoClass']} [{$task['id']}] bot={$task['botId']}";
            if (isset($task['orderingKey'])) {
                echo " order={$task['orderingKey']}";
            }
            echo "\n";
        }
        if ($tasks === []) {
            echo "  (empty)\n";
        }
    } else {
        out($tasks, true);
    }
    exit(0);
}

// ---- Delayed ----
if (isset($options['delayed'])) {
    $now = time();
    $delayedIdsWithScores = $redis->zRange('tg_outbound:delayed', 0, -1, true);
    $globalDelayed = $redis->zRange('tg_outbound:global:delayed', 0, -1, true);
    $totalItems = count($delayedIdsWithScores) + count($globalDelayed);

    if (!$jsonOutput) {
        echo "=== Delayed Tasks ({$totalItems}) ===\n";
        if (count($delayedIdsWithScores) > 0) {
            echo "--- Per-key delayed (" . count($delayedIdsWithScores) . ") ---\n";
        }
        $minDelay = PHP_INT_MAX;
        $maxDelay = 0;
        foreach ($delayedIdsWithScores as $deliveryId => $score) {
            $envelopeJson = $redis->get('tg_outbound:delayed:data:' . $deliveryId);
            if ($envelopeJson === false || $envelopeJson === null) {
                continue;
            }
            $data = json_decode((string)$envelopeJson, true);
            $task = $data['task'] ?? [];
            $state = $data['state'] ?? [];
            $wait = (int)$score - $now;
            if ($wait < $minDelay) {
                $minDelay = $wait;
            }
            if ($wait > $maxDelay) {
                $maxDelay = $wait;
            }
            echo "  {$task['id']}: {$task['dtoClass']} attempt={$state['attempt']} wait={$wait}s error={$state['lastError']}\n";
        }
        echo "\n";
        if (count($globalDelayed) > 0) {
            echo "--- Broadcast delayed (" . count($globalDelayed) . ") ---\n";
            foreach ($globalDelayed as $envelopeJson => $score) {
                $data = json_decode((string)$envelopeJson, true);
                $task = $data['task'] ?? [];
                $state = $data['state'] ?? [];
                $wait = (int)$score - $now;
                echo "  {$task['id']}: {$task['dtoClass']} attempt={$state['attempt']} wait={$wait}s error={$state['lastError']}\n";
            }
            echo "\n";
        }
        echo "min delay: {$minDelay}s, max delay: {$maxDelay}s\n";
    } else {
        $items = [];
        foreach ($delayedIdsWithScores as $deliveryId => $score) {
            $envelopeJson = $redis->get('tg_outbound:delayed:data:' . $deliveryId);
            if ($envelopeJson === false || $envelopeJson === null) {
                continue;
            }
            $data = json_decode((string)$envelopeJson, true);
            $task = $data['task'] ?? [];
            $state = $data['state'] ?? [];
            $items[] = [
                'task_id' => $task['id'],
                'dto_class' => $task['dtoClass'],
                'bot_id' => $task['botId'],
                'attempt' => $state['attempt'] ?? 0,
                'available_at' => (int)$score,
                'wait_sec' => (int)$score - $now,
                'last_error' => $state['lastError'] ?? null,
            ];
        }
        foreach ($globalDelayed as $envelopeJson => $score) {
            $data = json_decode((string)$envelopeJson, true);
            $task = $data['task'] ?? [];
            $state = $data['state'] ?? [];
            $items[] = [
                'task_id' => $task['id'],
                'dto_class' => $task['dtoClass'],
                'bot_id' => $task['botId'],
                'attempt' => $state['attempt'] ?? 0,
                'available_at' => (int)$score,
                'wait_sec' => (int)$score - $now,
                'last_error' => $state['lastError'] ?? null,
                'type' => 'broadcast',
            ];
        }
        out($items, true);
    }
    exit(0);
}

// ---- Bottlenecks ----
if (isset($options['bottlenecks'])) {
    $iterator = null;
    $patterns = [
        'tg_outbound:stats:*:retry:*',
        'tg_outbound:stats:*:failed:*',
    ];

    $counts = [];
    foreach ($patterns as $pattern) {
        while ($keys = $redis->scan($iterator, $pattern)) {
            foreach ($keys as $key) {
                $val = (int)$redis->get($key);
                if ($val > 0) {
                    $shortKey = preg_replace('#^tg_outbound:stats:\d+:#', '', $key);
                    $counts[$shortKey] = ($counts[$shortKey] ?? 0) + $val;
                }
            }
        }
    }

    arsort($counts);

    if (!$jsonOutput) {
        echo "=== Bottlenecks (top retry/fail reasons) ===\n";
        foreach (array_slice($counts, 0, $limit) as $key => $count) {
            echo "  {$key}: {$count}\n";
        }
        if ($counts === []) {
            echo "  (no data)\n";
        }
    } else {
        out($counts, true);
    }
    exit(0);
}

// No action specified
echo "No action specified. Use --help to see available options.\n";
exit(1);
