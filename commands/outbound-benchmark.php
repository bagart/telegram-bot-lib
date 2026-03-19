<?php

declare(strict_types=1);

use BAGArt\ASKClient\Contracts\Transporting\HttpTransportContract;
use BAGArt\ASKClient\Lockers\InMemoryLocker;
use BAGArt\ASKClient\Request\ASKHttpRequest;
use BAGArt\ASKClient\Transporting\HttpTransports\ASKSocketTransport;
use BAGArt\ASKClient\Transporting\HttpTransports\CurlMultiTransport;
use BAGArt\ASKClient\Transporting\HttpTransports\GuzzleTransport;
use BAGArt\AsyncKernel\ASKClock;
use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\AsyncKernel\Cache\InMemoryCache;
use BAGArt\AsyncKernel\Contracts\Daemons\ASKTickableContract;
use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\AsyncKernel\Promise\ASKPromiseResolver;
use BAGArt\AsyncKernel\Wrappers\ASKCacheWrapper;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundCircuitBreakerContract;
use BAGArt\TelegramBot\Contracts\Outbound\OutboundRateLimiterContract;
use BAGArt\TelegramBot\Outbound\Adapters\InMemoryOutboundQueue;
use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\CircuitBreakerState;
use BAGArt\TelegramBot\Outbound\Config\OutboundWorkerConfig;
use BAGArt\TelegramBot\Outbound\ExpiryMiddleware;
use BAGArt\TelegramBot\Outbound\LeaseRenewer;
use BAGArt\TelegramBot\Outbound\OutboundEnvelope;
use BAGArt\TelegramBot\Outbound\OutboundMiddleware;
use BAGArt\TelegramBot\Outbound\OutboundPipeline;
use BAGArt\TelegramBot\Outbound\OutboundRetryException;
use BAGArt\TelegramBot\Outbound\OutboundTask;
use BAGArt\TelegramBot\Outbound\RateLimitMiddleware;
use BAGArt\TelegramBot\Outbound\RetryBudgetMiddleware;
use BAGArt\TelegramBot\Outbound\TgOutboundDaemon;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;

require_once __DIR__.'/../../../../vendor/autoload.php';

// ── Options ──────────────────────────────────────────────────────────────────

$definedOptions = [
    'transport::', 'host::', 'orig', 'rate::', 'duration::', 'runs::',
    'port::', 'token::', 'warmup::', 'help',
];

$options = getopt('', $definedOptions);

if (isset($options['help'])) {
    echo "Usage: php commands/outbound-benchmark.php [options]

Compare HTTP transports through the outbound daemon pipeline
with rate limiting. Each transport gets a warmup phase first,
then runs 'runs' measurements. Average results shown.

Options:
  --transport=<type>  Transport: curl-multi, guzzle, ask-socket (default: all)
  --host=<host>       Target host (default: localhost, starts PHP server)
  --orig              Use Telegram API (requires TELEGRAM_BOT_TOKEN or --token)
  --rate=<n>          Rate limit, requests per second (default: 30)
  --duration=<n>      Measurement duration in seconds per run (default: 5)
  --runs=<n>          Measurement runs per transport, averaged (default: 3)
  --warmup=<n>        Warmup duration in seconds (default: 2)
  --port=<n>          Port for localhost server (default: 8080)
  --token=<token>     Bot token for --orig mode
  --help              This help

Examples:
  php commands/outbound-benchmark.php
  php commands/outbound-benchmark.php --transport=guzzle --rate=50
  php commands/outbound-benchmark.php --orig --token=123:abc --rate=20
  php commands/outbound-benchmark.php --host=192.168.1.100:8080
";
    exit(0);
}

$rateLimit = max(1, (int) ($options['rate'] ?? 30));
$duration = max(1, (int) ($options['duration'] ?? 5));
$runs = max(1, (int) ($options['runs'] ?? 3));
$warmupSec = max(0, (int) ($options['warmup'] ?? 2));
$port = max(1024, (int) ($options['port'] ?? 8080));
$token = (string) ($options['token'] ?? getenv('TELEGRAM_BOT_TOKEN') ?: '');

$transportFilter = (string) ($options['transport'] ?? '');
$useOrig = isset($options['orig']);
$customHost = (string) ($options['host'] ?? '');

// ── Transport factories ──────────────────────────────────────────────────────

function transportFactories(): array
{
    return [
        CurlMultiTransport::TYPE => fn () => new CurlMultiTransport(),
        GuzzleTransport::TYPE => fn () => new GuzzleTransport(),
        ASKSocketTransport::TYPE => fn () => new ASKSocketTransport(),
    ];
}

$factories = transportFactories();

if ($transportFilter !== '' && !isset($factories[$transportFilter])) {
    fwrite(STDERR, "Unknown transport: {$transportFilter}. Known: ".implode(', ', array_keys($factories))."\n");
    exit(2);
}

$transports = $transportFilter !== '' ? [$transportFilter] : array_keys($factories);

// ── Resolve target URL ───────────────────────────────────────────────────────

$targetUrl = '';

if ($useOrig) {
    if ($token === '') {
        fwrite(STDERR, "--orig mode requires TELEGRAM_BOT_TOKEN env var or --token option\n");
        exit(2);
    }
    $targetUrl = "https://api.telegram.org/bot{$token}/sendMessage";
} elseif ($customHost !== '') {
    $targetUrl = "http://{$customHost}/tg-bench.php";
} else {
    $targetUrl = "http://127.0.0.1:{$port}/tg-bench.php";
    $docRoot = dirname(__DIR__, 4).'/public';
    if ($docRoot !== false && is_dir($docRoot)) {
        $serverCmd = sprintf(
            'php -S localhost:%d -t %s > /dev/null 2>&1 & echo $!',
            $port,
            escapeshellarg($docRoot)
        );
        $pid = trim((string) shell_exec($serverCmd));
        if ($pid !== '' && is_numeric($pid)) {
            echo "  PHP server started (pid={$pid}) on localhost:{$port}\n";
            register_shutdown_function(function () use ($pid): void {
                shell_exec("kill {$pid} 2>/dev/null");
            });
            $ready = false;
            for ($i = 0; $i < 20; $i++) {
                $headers = @get_headers($targetUrl);
                if ($headers !== false && isset($headers[0]) && str_contains($headers[0], '200')) {
                    $ready = true;
                    break;
                }
                usleep(100_000);
            }
            if (!$ready) {
                fwrite(STDERR, "  WARNING: Local PHP server on {$targetUrl} did not respond within 2s. Continuing anyway...\n");
            }
        } else {
            echo "  Could not auto-start PHP server. Ensure {$targetUrl} is reachable.\n";
        }
    } else {
        echo "  public/ dir not found. Ensure {$targetUrl} is reachable.\n";
    }
}

// ── Benchmark rate limiter (sliding window, 1s buckets) ─────────────────────

final class BenchmarkRateLimiter implements OutboundRateLimiterContract
{
    /** @var array<string, list<float>> */
    private array $windows = [];

    public function __construct(
        private readonly int $rate,
    ) {
    }

    public function getRetryDelay(string $key): float
    {
        $this->gc($key);
        $slots = $this->windows[$key] ?? [];

        if (count($slots) >= $this->rate) {
            $oldest = $slots[0];
            $wait = $oldest - microtime(true) + 1.0;

            return max(0.0, $wait);
        }

        return 0.0;
    }

    public function markSent(string $key): void
    {
        $this->windows[$key][] = microtime(true);
    }

    public function registerRetryAfter(string $key, float $seconds): void
    {
    }

    public function resetKey(string $key): void
    {
        unset($this->windows[$key]);
    }

    private function gc(string $key): void
    {
        $expired = microtime(true) - 1.0;
        $slots = $this->windows[$key] ?? [];
        $kept = [];

        foreach ($slots as $ts) {
            if ($ts > $expired) {
                $kept[] = $ts;
            }
        }

        $this->windows[$key] = $kept;
    }
}

// ── Benchmark executor ───────────────────────────────────────────────────────

final class BenchmarkOutboundExecutor implements OutboundMiddleware
{
    public int $sent = 0;
    public int $errors = 0;

    public function __construct(
        private readonly HttpTransportContract $transport,
        private readonly OutboundRateLimiterContract $rateLimiter,
        private readonly string $targetUrl,
    ) {
    }

    public function handle(OutboundEnvelope $envelope, \Closure $next): void
    {
        $body = json_encode($envelope->task->dtoData, JSON_THROW_ON_ERROR);

        $request = new ASKHttpRequest(
            url: $this->targetUrl,
            method: 'POST',
            headers: ['Content-Type: application/json'],
            body: $body,
            requestName: 'benchmark',
        );

        try {
            $this->transport->request($request);
            $key = $envelope->task->botConfig->botId.':'.$envelope->task->dtoClass.':bench';
            $this->rateLimiter->markSent($key);
            $this->sent++;
        } catch (Throwable $e) {
            $this->errors++;
            throw new OutboundRetryException(
                delaySec: 1,
                reason: 'transport_error',
                previous: $e,
            );
        }
    }
}

// ── Noop circuit breaker ─────────────────────────────────────────────────────

final class NoopCircuitBreaker implements OutboundCircuitBreakerContract
{
    public function allowsRequest(string $botId): bool
    {
        return true;
    }
    public function recordFailure(string $botId): void
    {
    }
    public function recordSuccess(string $botId): void
    {
    }
    public function getState(string $botId): CircuitBreakerState
    {
        return CircuitBreakerState::Closed;
    }
}

// ── Benchmark timer ──────────────────────────────────────────────────────────

final class BenchmarkTimer implements ASKTickableContract
{
    private readonly float $start;

    public function __construct(
        private readonly AsyncKernel $kernel,
        private readonly float $durationSec,
    ) {
        $this->start = microtime(true);
    }

    public function tick(int $systemPressure): void
    {
        if (microtime(true) - $this->start >= $this->durationSec) {
            $this->kernel->stop('benchmark_duration_elapsed');
        }
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
}

// ── Helpers ──────────────────────────────────────────────────────────────────

function drainTransport(HttpTransportContract $transport): void
{
    if (method_exists($transport, 'drain')) {
        $transport->drain();
    }
}

/**
 * Run one kernel phase with the given transport.
 *
 * @return array{0: int, 1: int, 2: float}  [sent, errors, wall_time]
 */
function runKernelPhase(
    HttpTransportContract $transport,
    BenchmarkRateLimiter $rateLimiter,
    string $targetUrl,
    int $rate,
    int $durationSec,
    int $taskMultiplier = 10,
): array {
    $clock = new ASKClock();
    $logger = new ASKLogWrapper(minLevel: 'debug');
    $resolver = new ASKPromiseResolver();

    $config = new OutboundWorkerConfig(
        maxAttempts: 5,
        maxConcurrentFibers: 500,
    );

    $cache = new ASKCacheWrapper(new InMemoryCache($clock));
    $locker = new InMemoryLocker();
    $outboundCache = new KernelCacheAdapter($cache, $locker);

    $queue = new InMemoryOutboundQueue($clock, 200_000);

    $executor = new BenchmarkOutboundExecutor(
        transport: $transport,
        rateLimiter: $rateLimiter,
        targetUrl: $targetUrl,
    );

    $pipeline = new OutboundPipeline([
        new ExpiryMiddleware($config->maxAgeSec, $config->minAttemptsForExpiry),
        new RetryBudgetMiddleware($config->maxAttempts),
        new RateLimitMiddleware($rateLimiter),
        $executor,
    ]);

    $stats = new TgOutboundStats($outboundCache, 1);
    $circuitBreaker = new NoopCircuitBreaker();
    $leaseRenewer = new LeaseRenewer($queue, $clock, 3600, 0);
    $scheduler = new ASKFiberScheduler();

    $daemon = new TgOutboundDaemon(
        queue: $queue,
        pipeline: $pipeline,
        circuitBreaker: $circuitBreaker,
        stats: $stats,
        leaseRenewer: $leaseRenewer,
        logger: $logger,
        config: $config,
        scheduler: $scheduler,
    );

    $totalTasks = $rate * $durationSec * $taskMultiplier;

    for ($i = 0; $i < $totalTasks; $i++) {
        $task = new OutboundTask(
            id: bin2hex(random_bytes(16)),
            botConfig: new TgBotConfig(token: 'bench:token', botId: 'bench-bot'),
            dtoClass: 'BenchSendMessage',
            dtoData: ['data' => (string) random_int(0, PHP_INT_MAX)],
        );
        $queue->push($task);
    }

    $kernel = new AsyncKernel(logger: $logger, shutdownTimeout: 30);
    $kernel->addTickable($transport);
    $kernel->addTickable($resolver);
    $kernel->addDaemon($daemon);
    $kernel->addTickable(new BenchmarkTimer($kernel, $durationSec));

    $wallStart = microtime(true);
    $kernel->run();
    $wallTime = microtime(true) - $wallStart;

    return [$executor->sent, $executor->errors, $wallTime];
}

function average(array $values): float
{
    $n = count($values);

    return $n > 0 ? array_sum($values) / $n : 0.0;
}

// ── Run one transport (warmup + measurement runs) ────────────────────────────

function runBenchmark(
    string $name,
    callable $makeTransport,
    string $targetUrl,
    int $rate,
    int $durationSec,
    int $warmupSec,
    int $runs,
): array {
    $transport = $makeTransport();

    // ── Warmup ──
    if ($warmupSec > 0) {
        $warmupLimiter = new BenchmarkRateLimiter($rate);
        [$wSent, $wErrors, $wTime] = runKernelPhase(
            transport: $transport,
            rateLimiter: $warmupLimiter,
            targetUrl: $targetUrl,
            rate: $rate,
            durationSec: $warmupSec,
            taskMultiplier: 5,
        );
        drainTransport($transport);
        echo "            warmup sent={$wSent} in ".number_format($wTime, 2)."s\n";
    }

    // ── Measurement runs ──
    $sentValues = [];
    $elapsedValues = [];
    $errorValues = [];

    for ($i = 0; $i < $runs; $i++) {
        $runLimiter = new BenchmarkRateLimiter($rate);
        [$s, $e, $t] = runKernelPhase(
            transport: $transport,
            rateLimiter: $runLimiter,
            targetUrl: $targetUrl,
            rate: $rate,
            durationSec: $durationSec,
            taskMultiplier: 10,
        );
        $sentValues[] = $s;
        $elapsedValues[] = $t;
        $errorValues[] = $e;
        drainTransport($transport);
    }

    $sentAvg = (int) round(average($sentValues));
    $elapsedAvg = average($elapsedValues);
    $errorsMax = $errorValues !== [] ? (int) max($errorValues) : 0;
    $throughput = $elapsedAvg > 0 ? $sentAvg / $elapsedAvg : 0.0;
    $pctOfLimit = $rate > 0 ? ($throughput / $rate) * 100 : 0.0;

    echo "            \x1b[1mavg sent={$sentAvg}, errors={$errorsMax}, "
        .number_format($throughput, 1)." msgs/s ({$pctOfLimit}% of limit)\x1b[0m\n";

    return [
        'sent' => $sentAvg,
        'errors' => $errorsMax,
        'elapsed' => $elapsedAvg,
        'throughput' => $throughput,
        'pctOfLimit' => $pctOfLimit,
    ];
}

// ── Banner ───────────────────────────────────────────────────────────────────

echo "=== Outbound Transport Benchmark ===\n";
echo '    PHP: '.PHP_VERSION."\n";
echo "    Rate limit: {$rateLimit} req/s\n";
echo "    Measurement: {$duration}s × {$runs} runs (averaged)\n";
echo "    Warmup: {$warmupSec}s\n";
echo "    Target: {$targetUrl}\n";
echo "    Transports: ".implode(', ', $transports)."\n\n";

// ── Measurements ─────────────────────────────────────────────────────────────

$results = [];
$cellIndex = 0;
$cellTotal = count($transports) * $runs;

foreach ($transports as $transport) {
    $cellIndex++;
    echo "  [{$cellIndex}/".count($transports)."] {$transport}\n";

    try {
        $results[$transport] = runBenchmark(
            name: $transport,
            makeTransport: $factories[$transport],
            targetUrl: $targetUrl,
            rate: $rateLimit,
            durationSec: $duration,
            warmupSec: $warmupSec,
            runs: $runs,
        );
    } catch (Throwable $e) {
        echo "    ERROR: {$e->getMessage()}\n";
        $results[$transport] = [
            'sent' => 0,
            'errors' => 0,
            'elapsed' => 0.0,
            'throughput' => 0.0,
            'pctOfLimit' => 0.0,
        ];
    }
}

// ── Final comparison table ───────────────────────────────────────────────────

$header = sprintf(
    "%-16s %8s %7s %9s %10s %8s",
    'transport',
    'sent',
    'errors',
    'msgs/s',
    '% of limit',
    'elapsed'
);
echo "\n".$header."\n";
echo str_repeat('-', strlen($header))."\n";

foreach ($results as $transport => $r) {
    echo sprintf(
        "%-16s %8d %7d %9.1f %9.1f%% %8.2fs\n",
        $transport,
        (int) round($r['sent']),
        $r['errors'],
        $r['throughput'],
        min($r['pctOfLimit'], 100.0),
        $r['elapsed'],
    );
}

// ── Ranking ──────────────────────────────────────────────────────────────────

echo "\nRanked by throughput (highest → lowest):\n";
$ranked = $results;
uasort($ranked, fn ($a, $b) => $b['throughput'] <=> $a['throughput']);
$rank = 0;

foreach ($ranked as $transport => $r) {
    $rank++;
    echo sprintf(
        "  %d. %-16s %6.1f msgs/s  (%5.1f%% of rate limit)\n",
        $rank,
        $transport,
        $r['throughput'],
        min($r['pctOfLimit'], 100.0),
    );
}

echo "\nDone.\n";
