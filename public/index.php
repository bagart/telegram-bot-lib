<?php

declare(strict_types=1);

/**
 * Universal webhook entry point.
 *
 * Dispatcher is resolved by priority:
 *   1. ?dispatcher= query parameter (highest)
 *   2. Current script filename
 *   3. Default: sync
 *
 * Transport is resolved similarly:
 *   1. ?transport= query parameter
 *   2. Current script filename convention
 *   3. Default: sync
 *
 * Usage: point your web server to telegram-bot-lib/public/
 *   ?token=       Bot token (required)
 *   ?dispatcher=  sync|queue|async  (auto-detected from filename)
 *   ?transport=   sync|async        (auto-detected from filename)
 *   ?echo         Echo reply to messages
 *   ?show         Dump update to console
 *   ?log          Log to stderr
 *   ?store        Store messages to SQLite
 */

use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;

extract(require __DIR__.'/config.php');

// Resolve dispatcher: query param > filename > default async
$dispatcherType = $_GET['dispatcher'] ?? null;
if ($dispatcherType === null || $dispatcherType === '') {
    $basename = basename($_SERVER['SCRIPT_FILENAME'] ?? __FILE__, '.php');
    $dispatcherType = match ($basename) {
        'index-queue' => 'queue',
        'index-async' => 'async',
        default => 'async',
    };
}
$serviceConfig->dispatcher = $dispatcherType;

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

$factory = TgBotSetupFactory::build();
$setup = $factory->create(serviceConfig: $serviceConfig, initProcConfig: $initProcConfig);

if ($serviceConfig->dispatcher === 'sync') {
    $result = TgBotSetupFactory::webhook(
        processorRegistry: $setup->processorRegistry,
        setup: $setup,
        serviceConfig: $serviceConfig,
    )->parse(data: $data, secret: $providedSecret, config: $serviceConfig);

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['ok' => $result]);
    exit;
}

$dispatcher = $factory->dispatcherRegistry->make(
    dispatcherType: $serviceConfig->dispatcher,
    scheduler: new ASKFiberScheduler(),
    logger: $setup->logger,
);

$dtoMapper = $factory->dtoMapper($serviceConfig);
$updateDTO = $dtoMapper->fromArray(UpdateTypeDTO::class, $data);
assert($updateDTO instanceof UpdateTypeDTO);

$selector = new RegisteredUpdateProcessorSelector(
    serviceConfig: $serviceConfig,
    botSetup: $setup,
);

$botConfig = new TgBotConfig(token: $botConfig->token);

$dispatched = 0;
$selectProcessors = $selector->selectProcessors($updateDTO, $botConfig);
foreach ($selectProcessors as $property => $processors) {
    foreach ($processors as $processor) {
        $dispatcher->dispatch(
            serviceConfig: $serviceConfig,
            botConfig: $botConfig,
            dto: $updateDTO->{$property},
            processors: [$processor],
            action: $property,
        );
        ++$dispatched;
    }
}

// Respond immediately
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'dispatched' => $dispatched, 'dispatcher' => $serviceConfig->dispatcher]);

// For async: run event loop after flushing response
if ($scheduler !== null) {
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    $scheduler->run();
}
