<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Outbound\Adapters\KernelCacheAdapter;
use BAGArt\TelegramBot\Outbound\TgOutboundStats;

if (!function_exists('makeCacheWrapper')) {
    require_once __DIR__.'/../../Helpers.php';
}

describe('TgOutboundStats', function () {
    function statsCache(): KernelCacheAdapter
    {
        return new KernelCacheAdapter(
            makeCacheWrapper(),
            new \BAGArt\ASKClient\Lockers\InMemoryLocker(),
        );
    }

    it('recordSent increments global and per-bot counters', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordSent('bot1', 'sendMessage');
        $stats->recordSent('bot1', 'sendMessage');

        $hour = date('YmdH');
        expect($cache->get("tg_outbound:stats:{$hour}:sent:global"))->toBe(2)
            ->and($cache->get("tg_outbound:stats:{$hour}:sent:bot1:sendMessage"))->toBe(2);
    });

    it('recordRetry increments retry counters with reason', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordRetry('bot1', 'sendMessage', 'rate_limit');

        $hour = date('YmdH');
        expect($cache->get("tg_outbound:stats:{$hour}:retry:rate_limit"))->toBe(1)
            ->and($cache->get("tg_outbound:stats:{$hour}:retry:bot1:sendMessage:rate_limit"))->toBe(1);
    });

    it('recordFailed increments failed counters', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordFailed('bot1', 'sendMessage', 'network_timeout');

        $hour = date('YmdH');
        expect($cache->get("tg_outbound:stats:{$hour}:failed:network_timeout"))->toBe(1);
    });

    it('recordBusinessError increments business error counters', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordBusinessError('bot1', 'sendMessage', 400);

        $hour = date('YmdH');
        expect($cache->get("tg_outbound:stats:{$hour}:business_error:400"))->toBe(1);
    });

    it('recordDlqPushed increments dlq_pushed counter', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordDlqPushed('bot1');

        $hour = date('YmdH');
        expect($cache->get("tg_outbound:stats:{$hour}:dlq_pushed:bot1"))->toBe(1)
            ->and($cache->get("tg_outbound:stats:{$hour}:dlq_pushed:total"))->toBe(1);
    });

    it('recordDlqRetried increments dlq_retried counter', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordDlqRetried('bot1');

        $hour = date('YmdH');
        expect($cache->get("tg_outbound:stats:{$hour}:dlq_retried:bot1"))->toBe(1)
            ->and($cache->get("tg_outbound:stats:{$hour}:dlq_retried:total"))->toBe(1);
    });

    it('getState returns dlq_pushed, dlq_retried and dlq_purged per hour', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordDlqPushed('bot1');
        $stats->recordDlqRetried('bot1');
        $stats->recordDlqPurged(5);

        $state = $stats->getState();
        $hour = date('YmdH');
        $hourState = $state["hour_{$hour}"];

        expect($hourState['dlq_pushed'])->toBe(1)
            ->and($hourState['dlq_retried'])->toBe(1)
            ->and($hourState['dlq_purged'])->toBe(5);
    });

    it('getGlobalMetrics reports dlq counters when present', function () {
        $cache = statsCache();
        $stats = new TgOutboundStats($cache);

        $stats->recordDlqPushed('bot1');
        $stats->recordDlqRetried('bot1');
        $stats->recordDlqPurged(3);

        $hour = date('YmdH');
        $metrics = $stats->getGlobalMetrics($hour, $hour);

        expect($metrics)->toHaveKey("{$hour}:dlq_pushed")
            ->and($metrics["{$hour}:dlq_pushed"])->toBe(1)
            ->and($metrics)->toHaveKey("{$hour}:dlq_retried")
            ->and($metrics["{$hour}:dlq_retried"])->toBe(1)
            ->and($metrics)->toHaveKey("{$hour}:dlq_purged")
            ->and($metrics["{$hour}:dlq_purged"])->toBe(3);
    });
});
