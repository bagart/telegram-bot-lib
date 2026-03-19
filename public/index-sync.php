<?php

declare(strict_types=1);

/**
 * Synchronous webhook entry point.
  */

use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\SyncProcessingDispatcher;
use BAGArt\TelegramBot\TgBotSetupFactory;

extract(require __DIR__.'/config.php');

$serviceConfig->dispatcher = SyncProcessingDispatcher::TYPE;

// Validate Telegram origin IP
$ipValidator = new TelegramIpValidator();
if (!$ipValidator->validate($_SERVER['REMOTE_ADDR'] ?? '')) {
    http_response_code(403);
    exit;
}

// Validate secret: botId:sha256(tokenPart)
$secretService = new AutoSecretByTokenService();
$expectedSecret = $secretService->secret($botConfig->token);
$providedSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;

if (!hash_equals($expectedSecret, $providedSecret ?? '')) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    exit;
}

$registry = TgBotSetupFactory::processorRegistry($initProcConfig);

// Parse and process synchronously
$result = TgBotSetupFactory::webhook($registry)
    ->parse(data: $data, secret: $providedSecret, config: $serviceConfig);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => $result]);
