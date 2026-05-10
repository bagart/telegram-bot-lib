<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\LaravelQueueDtoPipelineDispatcher;
use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers\SyncDtoPipelineDispatcher;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

function initUpdatePollerConfig(array $options, TgUpdateExampleConfig $config): void
{
    if (isset($options['poller'])) {
        $config->poller = $options['poller'];
    } elseif (isset($options['sync'])) {
        $config->poller = 'sync';
        $config->dispatcher = SyncDtoPipelineDispatcher::TYPE;
    }

    if (isset($options['dispatcher'])) {
        $config->dispatcher = $options['dispatcher'];
    } elseif (isset($options['queue'])) {
        $config->dispatcher = LaravelQueueDtoPipelineDispatcher::TYPE;
    }

    $config->echo = isset($options['echo']);
    $config->log = isset($options['log']);
    $config->store = isset($options['store']);
    $config->show = isset($options['show']);
    $config->bot->logLevel = $options['log-level'] ?? TgBotLogWrapper::LEVEL_INFO;
    $config->noAck = isset($options['no-ack']);

    echo "=== Poller Mode: {$config->poller} => {$config->dispatcher} ===\n";

    $flags = implode(' ', array_filter([
        $config->noAck ? '[NO-ACK]' : '[ACK]',
        $config->echo ? '[ECHO]' : null,
        $config->show ? '[SHOW]' : null,
        $config->store ? '[STORE]' : null,
        $config->log ? '[LOG]' : null,
        $config->bot->logLevel === TgBotLogWrapper::LEVEL_DEBUG ? '[DBG]' : null,
    ]));

    echo "Flags: {$flags}\n";
}
