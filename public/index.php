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
use BAGArt\TelegramBot\Contracts\TgUpdateProcessor\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\AnyDTOToLoggerProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageDTOEchoToUserProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageDTOShowToConsoleProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageDTOToDbProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;

require_once __DIR__.'/../../../../vendor/autoload.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    http_response_code(400);
    exit;
}
$config = new TgUpdateExampleConfig(
    bot: new TgBotConfig(token: $token),
);

initUpdatePollerConfig(
    array_intersect_key($_REQUEST, ['echo', 'show', 'log', 'store']),
    $config,
);

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

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    exit;
}

/** @var TgTypeDTOProcessorContract[]|string[] $processors */
$processors = array_keys(array_filter([
    MessageDTOEchoToUserProcessor::class => $config->echo,
    AnyDTOToLoggerProcessor::class => $config->log,
    MessageDTOToDbProcessor::class => $config->store,
    MessageDTOShowToConsoleProcessor::class => $config->show,
]));

$registry = TypeDTOProcessorRegistry::build();
foreach ($processors as $processor) {
    $registry->register(
        dtoClass: MessageTypeDTO::class,
        processor: $processor
    );
}

// Parse and process
$botId = explode(':', $token)[0];
$result = TgPureFactory::webhook($registry)
    ->parse($data, $botId, config: $config);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => $result]);
