<?php

declare(strict_types=1);

use BAGArt\AsyncKernel\Drivers\ASKFiberScheduler;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Http\Pure\Validators\TelegramIpValidator;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\AsyncFiberProcessingDispatcher;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;

extract(require __DIR__.'/config.php');
$serviceConfig->dispatcher = AsyncFiberProcessingDispatcher::TYPE;

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

// Create async dispatcher via TgBotSetupFactory
$factory = TgBotSetupFactory::build();
$setup = $factory->create(serviceConfig: $serviceConfig, initProcConfig: $initProcConfig);
$dispatcher = $factory->dispatcherRegistry->make(
    dispatcherType: $serviceConfig->dispatcher,
    scheduler: new ASKFiberScheduler(),
    logger: $setup->logger,
);

// Parse UpdateTypeDTO
$dtoMapper = $factory->dtoMapper($serviceConfig);
$updateDTO = $dtoMapper->fromArray(UpdateTypeDTO::class, $data);
assert($updateDTO instanceof UpdateTypeDTO);

$selector = new RegisteredUpdateProcessorSelector(
    serviceConfig: $serviceConfig,
    botSetup: $setup,
);

$botConfig = new TgBotConfig(token: $botConfig->token);
$scheduler = new ASKFiberScheduler();

$enqueued = 0;
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
        ++$enqueued;
    }
}

// Respond immediately, then run async event loop
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'enqueued' => $enqueued]);

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
$scheduler->run();
