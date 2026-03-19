<?php

declare(strict_types=1);

use BAGArt\ASKClientRedis\Redis\Client\PhpRedisAdapter;
use BAGArt\ASKClientRedis\Redis\Contract\RedisClientContract;
use BAGArt\ASKClientRedis\Redis\RedisDsn;
use BAGArt\AsyncKernel\ASKClock;
use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKDaemonContract;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundOrderingQueueContract;
use BAGArt\TelegramBot\Outbound\OutboundQueueRegistry;

require_once __DIR__.'/../../../../vendor/autoload.php';

$allowedOptions = [
    'redis-host::',
    'redis-port::',
    'redis-timeout::',
    'interval::',
    'json',
    'memory-limit::',
    'help',
];

$options = CommandActions::parseOptions(
    getopt('', $allowedOptions),
    $allowedOptions,
);

CommandActions::initRuntime($options);

if (isset($options['help'])) {
    echo 'Usage:
php commands/outbound-metrics-daemon.php      # Metrics viewer (refreshes every N seconds)

Options:
  --redis-host=127.0.0.1                      # Redis host (default: 127.0.0.1)
  --redis-port=6379                           # Redis port (default: 6379)
  --redis-timeout=2.0                         # Redis connection timeout
  --interval=5                                # Refresh interval in seconds (default: 5)
  --json                                      # Output as JSON lines
  --memory-limit=128M                         # PHP memory limit
  --help
';
    exit(0);
}

$logger = new ASKLogWrapper(
    logger: new \Monolog\Logger('MetricsDaemon', [new \Monolog\Handler\StreamHandler('php://stderr')]),
    minLevel: ASKLogWrapper::LEVEL_INFO,
);

$redis = new PhpRedisAdapter(
    new RedisDsn(
        host: (string)($options['redis-host'] ?? '127.0.0.1'),
        port: (int)($options['redis-port'] ?? 6379),
        timeout: (float)($options['redis-timeout'] ?? 2.0),
    )
);

$clock = new ASKClock();
$queue = OutboundQueueRegistry::build()
    ->make(type: 'redis', clock: $clock, dsn: $redisDsn->toString());

$jsonOutput = isset($options['json']);
$interval = max(1, (int)($options['interval'] ?? 5));

final class MetricsViewerDaemon implements ASKDaemonContract, ASKTickableContract, WithASKTickableContract
{
    private int $tickCount = 0;

    public function __construct(
        private readonly RedisClientContract $redis,
        private readonly OutboundOrderingQueueContract $queue,
        private readonly bool $jsonOutput,
        private readonly int $interval,
        private readonly ASKLogWrapper $logger,
    ) {
    }

    public function tick(int $systemPressure): void
    {
        if ($this->tickCount > 0) {
            sleep($this->interval);
        }
        ++$this->tickCount;

        $ready = (int)$this->redis->zCard('tg_outbound:ready_keys');
        $delayed = (int)$this->redis->zCard('tg_outbound:delayed')
            + (int)$this->redis->zCard('tg_outbound:global:delayed');
        $inflight = (int)$this->redis->hLen('tg_outbound:inflight');

        $dlqChannels = $this->queue->getDlqChannels('tg-dlq:*');
        $dlqTotal = 0;
        foreach ($dlqChannels as $ch) {
            $dlqTotal += (int)$this->redis->hLen($ch);
        }

        $hourKey = 'tg_outbound:stats:'.date('YmdH');
        $sent = (int)$this->redis->get("{$hourKey}:sent:global");
        $retry = (int)$this->redis->get("{$hourKey}:retry:telegram_rate_limit");
        $failed = (int)$this->redis->get("{$hourKey}:failed:network");

        if ($this->jsonOutput) {
            echo json_encode([
                'ts' => time(),
                'queue' => ['ready' => $ready, 'delayed' => $delayed, 'inflight' => $inflight, 'dlq' => $dlqTotal],
                'current_hour' => ['sent' => $sent, 'retry' => $retry, 'failed' => $failed],
            ])."\n";
        } else {
            echo "\033[2J\033[H"; // Clear screen
            echo "=== Outbound Metrics (refreshing every {$this->interval}s) ===\n";
            echo "Time: " . date('Y-m-d H:i:s') . "\n\n";
            echo "Queue:\n";
            echo "  Ready:   {$ready}\n";
            echo "  Delayed: {$delayed}\n";
            echo "  Inflight: {$inflight}\n";
            echo "  DLQ:     {$dlqTotal} (channels: " . count($dlqChannels) . ")\n";
            echo "  Total:   " . ($ready + $delayed + $inflight + $dlqTotal) . "\n\n";
            echo "Current Hour ({$hourKey}):\n";
            echo "  Sent:   {$sent}\n";
            echo "  Retry:  {$retry}\n";
            echo "  Failed: {$failed}\n";
        }
    }

    public function tickable(): array
    {
        return [];
    }

    public function pressure(): int
    {
        return 0;
    }

    public function isIdle(): bool
    {
        return true;
    }

    public function queueSize(): int
    {
        return 0;
    }

    public function onError(Throwable $e): void
    {
        $this->logger->error("[MetricsDaemon] {$e->getMessage()}");
    }

    public function startup(): void
    {
        $this->logger->info('[MetricsDaemon] started');
    }

    public function shutdown(\BAGArt\AsyncKernel\ASKShutdownContext $context): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'MetricsViewerDaemon';
    }
}

$daemon = new MetricsViewerDaemon($redis, $queue, $jsonOutput, $interval, $logger);

try {
    new AsyncKernel(logger: $logger, clock: $clock)
        ->addDaemon($daemon)
        ->run();
} catch (Throwable $e) {
    $logger->emergency('Fatal metrics daemon crash', [
        'exception' => $e::class,
        'message' => $e->getMessage(),
    ]);
    exit(1);
}
