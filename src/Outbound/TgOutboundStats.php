<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Contracts\Outbound\OutboundCacheContract;
use DateTimeImmutable;

final class TgOutboundStats
{
    private const string KEY_PREFIX = 'tg_outbound:stats:';

    public function __construct(
        private readonly OutboundCacheContract $cache,
        private readonly int $retentionHours = 168,
    ) {
    }

    public function recordSent(string $botId, string $method): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:sent:global", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:sent:{$botId}:total", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:sent:{$botId}:{$method}", 1, $this->retentionHours * 3600);
    }

    public function recordRetry(string $botId, string $method, string $reason): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:retry:{$reason}", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:retry:{$botId}:total", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:retry:{$botId}:{$method}:{$reason}", 1, $this->retentionHours * 3600);
    }

    public function recordFailed(string $botId, string $method, string $reason): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:failed:{$reason}", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:failed:{$botId}:total", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:failed:{$botId}:{$method}:{$reason}", 1, $this->retentionHours * 3600);
    }

    public function recordBusinessError(string $botId, string $method, int $code): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:business_error:{$code}", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:business_error:{$botId}:total", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:business_error:{$botId}:{$method}:{$code}", 1, $this->retentionHours * 3600);
    }

    public function recordDlqPushed(string $botId): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:dlq_pushed:total", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:dlq_pushed:{$botId}", 1, $this->retentionHours * 3600);
    }

    public function recordDlqRetried(string $botId): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:dlq_retried:total", 1, $this->retentionHours * 3600);
        $this->cache->incrementWithTtl("{$hour}:dlq_retried:{$botId}", 1, $this->retentionHours * 3600);
    }

    public function recordDlqPurged(int $count): void
    {
        $hour = $this->hourKey();
        $this->cache->incrementWithTtl("{$hour}:dlq_purged", $count, $this->retentionHours * 3600);
    }

    /**
     * Aggregated metrics for all bots over a time range.
     *
     * @param  string  $fromHour  Start (format YmdH, e.g. '2026071014').
     * @param  string  $toHour  End (inclusive, same format).
     * @return array<string, int>
     */
    public function getGlobalMetrics(string $fromHour, string $toHour): array
    {
        return $this->collectMetrics(null, $fromHour, $toHour);
    }

    /**
     * Metrics for a single bot over a time range.
     *
     * @return array<string, int>
     */
    public function getBotMetrics(string $botId, string $fromHour, string $toHour): array
    {
        return $this->collectMetrics($botId, $fromHour, $toHour);
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        $state = [];
        for ($i = 0; $i < 24; $i++) {
            $hour = date('YmdH', time() - $i * 3600);
            $hourKey = self::KEY_PREFIX.$hour;

            $global = (int) $this->cache->get("{$hourKey}:sent:global");
            $dlqPushed = (int) $this->cache->get("{$hourKey}:dlq_pushed:total");
            $dlqRetried = (int) $this->cache->get("{$hourKey}:dlq_retried:total");
            $businessError = (int) $this->cache->get("{$hourKey}:business_error:400");
            $retryRateLimit = (int) $this->cache->get("{$hourKey}:retry:telegram_rate_limit");

            $hasActivity = $global > 0 || $dlqPushed > 0 || $dlqRetried > 0
                || $businessError > 0 || $retryRateLimit > 0;
            if ($hasActivity || $i < 2) {
                $state["hour_{$hour}"] = [
                    'sent_global' => $global,
                    'retry_total' => $retryRateLimit,
                    'failed_total' => (int) $this->cache->get("{$hourKey}:failed:fatal_worker_error"),
                    'business_error' => $businessError,
                    'dlq_pushed' => $dlqPushed,
                    'dlq_retried' => $dlqRetried,
                    'dlq_purged' => (int) $this->cache->get("{$hourKey}:dlq_purged"),
                ];
            }
        }

        return $state;
    }

    /**
     * Collects metrics by hour in the range [fromHour, toHour].
     *
     * @param  string|null  $botId  null — global counters; otherwise per-bot (by ':total').
     * @return array<string, int>
     */
    private function collectMetrics(?string $botId, string $fromHour, string $toHour): array
    {
        $metrics = [];

        foreach ($this->hourRange($fromHour, $toHour) as $hour) {
            $hk = self::KEY_PREFIX.$hour;

            // global: sent:global, retry:{reason}, failed:{reason}, business_error:{code}, dlq_*:{suffix}
            // per-bot: sent:{botId}:total, retry:{botId}:total, failed:{botId}:total, ...
            $sent = $botId === null
                ? (int) $this->cache->get("{$hk}:sent:global")
                : (int) $this->cache->get("{$hk}:sent:{$botId}:total");

            if ($botId === null) {
                $retryRateLimit = (int) $this->cache->get("{$hk}:retry:telegram_rate_limit");
                $failedNetwork = (int) $this->cache->get("{$hk}:failed:network_timeout");
                $businessErrors = (int) $this->cache->get("{$hk}:business_error:400");
            } else {
                $retryRateLimit = (int) $this->cache->get("{$hk}:retry:{$botId}:total");
                $failedNetwork = (int) $this->cache->get("{$hk}:failed:{$botId}:total");
                $businessErrors = (int) $this->cache->get("{$hk}:business_error:{$botId}:total");
            }
            $retryCircuit = (int) $this->cache->get("{$hk}:retry:circuit_breaker");
            $failedFatal = (int) $this->cache->get("{$hk}:failed:fatal_worker_error");
            $dlqPushed = (int) $this->cache->get("{$hk}:dlq_pushed:".($botId ?? 'total'));
            $dlqRetried = (int) $this->cache->get("{$hk}:dlq_retried:".($botId ?? 'total'));
            $dlqPurged = (int) $this->cache->get("{$hk}:dlq_purged");

            $hasActivity = $sent > 0 || $retryRateLimit > 0 || $failedNetwork > 0
                || $businessErrors > 0 || $dlqPushed > 0 || $dlqRetried > 0 || $dlqPurged > 0;
            if (! $hasActivity) {
                continue;
            }

            $metrics["{$hour}:sent"] = ($metrics["{$hour}:sent"] ?? 0) + $sent;
            $metrics["{$hour}:retry:rate_limit"] = ($metrics["{$hour}:retry:rate_limit"] ?? 0) + $retryRateLimit;
            $metrics["{$hour}:retry:circuit_breaker"] = ($metrics["{$hour}:retry:circuit_breaker"] ?? 0) + $retryCircuit;
            $metrics["{$hour}:failed:network"] = ($metrics["{$hour}:failed:network"] ?? 0) + $failedNetwork;
            $metrics["{$hour}:failed:fatal"] = ($metrics["{$hour}:failed:fatal"] ?? 0) + $failedFatal;
            $metrics["{$hour}:business_error"] = ($metrics["{$hour}:business_error"] ?? 0) + $businessErrors;
            $metrics["{$hour}:dlq_pushed"] = ($metrics["{$hour}:dlq_pushed"] ?? 0) + $dlqPushed;
            $metrics["{$hour}:dlq_retried"] = ($metrics["{$hour}:dlq_retried"] ?? 0) + $dlqRetried;
            $metrics["{$hour}:dlq_purged"] = ($metrics["{$hour}:dlq_purged"] ?? 0) + $dlqPurged;
        }

        return $metrics;
    }

    /**
     * List of hours in YmdH format from $from to $to (inclusive).
     *
     * @return list<string>
     */
    private function hourRange(string $fromHour, string $toHour): array
    {
        $current = DateTimeImmutable::createFromFormat('YmdH', $fromHour) ?: (new DateTimeImmutable())->modify('-1 hour');
        $end = DateTimeImmutable::createFromFormat('YmdH', $toHour) ?: (new DateTimeImmutable())->modify('-1 hour');

        $hours = [];
        // Limit 720 hours (30 days) — guard against infinite loop on invalid input.
        for ($i = 0; $i < 720 && $current <= $end; $i++) {
            $hours[] = $current->format('YmdH');
            $current = $current->modify('+1 hour');
        }

        return $hours;
    }

    private function hourKey(): string
    {
        return self::KEY_PREFIX.date('YmdH');
    }
}
