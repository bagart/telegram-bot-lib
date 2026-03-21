<?php

declare(strict_types=1);

/**
 * Pure PHP webhook entry point.
 *
 * Usage: point your web server to telegram-bot-lib/public/
 * Token is passed as query param: ?token=YOUR_BOT_TOKEN
 * Telegram sends secret in header: X-Telegram-Bot-Api-Secret-Token
 */

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageEchoProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessagePdoStoreProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateLoggerProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;

require_once __DIR__.'/../../../../vendor/autoload.php';

// Token
$token = $_GET['token'] ?? null;
if (!$token) {
    http_response_code(400);
    exit;
}

// Validate IP
$ipValidator = new TelegramIpValidator();
if (!$ipValidator->validate($_SERVER['REMOTE_ADDR'] ?? '')) {
    http_response_code(403);
    exit;
}

// Validate secret: botId:sha256(tokenPart)
$secretService = new AutoSecretByTokenService();
$expectedSecret = $secretService->secret($token);
$providedSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;

if (!hash_equals($expectedSecret, $providedSecret ?? '')) {
    http_response_code(403);
    exit;
}

// Input
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    exit;
}

// Processors
$registry = new TypeDTOProcessorRegistry();

$echoMode = true;
if ($echoMode) {
    $registry->register(
        MessageTypeDTO::class,
        new MessageEchoProcessor(
            dtoClient: TgPureFactory::dtoClient(),
            logger: TgPureFactory::logger(),
            token: $token,
        )
    );
}


$logMode = true;
if ($logMode) {
    $registry->register(
        MessageTypeDTO::class,
        new UpdateLoggerProcessor(
            logger: TgPureFactory::logger(),
            namer: new TgEntityNamer(),
        )
    );
}


$logMode = isset($_GET['log']) && $_GET['log'] === '1';
if ($logMode) {
    $registry->register(
        MessageTypeDTO::class,
        new UpdateLoggerProcessor(
            logger: TgPureFactory::logger(),
            namer: new TgEntityNamer(),
        )
    );
}

$storeMode = true;
if ($storeMode) {
    $registry->register(
        MessageTypeDTO::class,
        new MessagePdoStoreProcessor(
            pdo: TgPureFactory::pdo(),
        )
    );
}

// Parse and process
$botId = explode(':', $token)[0];
$result = TgPureFactory::webhook($registry)
    ->parse($data, $botId);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => $result]);
