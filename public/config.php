<?php

declare(strict_types=1);

/**
 * Shared webhook configuration bootstrap.
 *
 * Returns a bare TgServiceConfig with the bot token from
 * the query string. Each index entry point includes this,
 * then overrides dispatcher, transport, and flags as
 * appropriate for its mode.
 *
 * Usage in entry points:
 *   $config = require __DIR__.'/config.php';
 *   $config->dispatcher = SyncDtoPipelineDispatcher::TYPE;
 */
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\ProcessorConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;

require_once __DIR__.'/../../../../vendor/autoload.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ?token= parameter']);
    exit;
}

return [
    'serviceConfig' => new TgServiceConfig(),
    'botConfig' => new TgBotConfig(token: $token),
    'initProcConfig' => new ProcessorConfig(
        echo: array_key_exists('echo', $_GET),
        show: array_key_exists('show', $_GET),
        log: array_key_exists('log', $_GET),
        store: array_key_exists('store', $_GET),
        dbg: array_key_exists('dbg', $_GET),
        antispam: array_key_exists('antispam', $_GET),
    ),
];
