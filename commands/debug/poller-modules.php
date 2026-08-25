<?php

declare(strict_types=1);

/**
 * Debug/dev long-polling entry with the FULL Laravel module pipeline.
 *
 * commands/poller-daemon.php builds a lib-only processor registry (echo/show/
 * store demo processors), so module processors (/voice, /text, …) never run
 * in that context. This script boots through the host Laravel kernel instead:
 * the container's TgBotSetupFactory shares the TypeDTOProcessorRegistry that
 * ModuleBootloader populated at boot, so every bootloaded module — and the
 * command registry — is visible here, exactly like the webhook path
 * (TgWebhookRequestParser + RegisteredUpdateProcessorSelector).
 *
 * Usage (inside the php-fpm/workspace container, from /var/www):
 *   php misc/BAGArt/telegram-bot-lib/commands/debug/poller-modules.php \
 *       --token=123:abc [--timeout=30] [--limit=100]
 *
 * TELEGRAM_BOT_TOKEN env is honored when --token is omitted (same resolution
 * order as CommandActions::resolveToken()).
 */

use BAGArt\AsyncKernel\AsyncKernel;
use BAGArt\TelegramBot\ApiCommunication\Polling\PollerState;
use BAGArt\TelegramBot\ApiCommunication\Polling\TgPollerDaemon;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgPollerConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\Modules\ModuleEnablementContract;
use BAGArt\TelegramBot\Processing\ErrorHandling\ErrorActions\LogErrorAction;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorConsumer;
use BAGArt\TelegramBot\Processing\ErrorHandling\ProcessingErrorRegistry;
use BAGArt\TelegramBot\Processing\ProcessorUpdateDaemon;
use BAGArt\TelegramBot\Processing\ProcessingDispatchers\SyncProcessingDispatcher;
use BAGArt\TelegramBot\Processing\RegisteredUpdateProcessorSelector;
use BAGArt\TelegramBot\Processing\Update\UpdateRouter;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../../vendor/autoload.php';

$options = getopt('', ['token::', 'timeout::', 'limit::', 'help']);

if (isset($options['help'])) {
    echo "Usage: php debug/poller-modules.php --token=xxx:yyy [--timeout=30] [--limit=100]\n".
         "  Long-polling daemon with the full Laravel module pipeline\n".
         "  (sync dispatcher, container processor registry, module enablement).\n";
    exit(0);
}

$token = $options['token'] ?? getenv('TELEGRAM_BOT_TOKEN') ?: null;

if (! is_string($token) || $token === '') {
    fwrite(STDERR, "No token: pass --token=xxx:yyy or set TELEGRAM_BOT_TOKEN\n");
    exit(1);
}

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../../../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$serviceConfig = new TgServiceConfig();
$serviceConfig->dispatcher = SyncProcessingDispatcher::TYPE;

$factory = $app->make(TgBotSetupFactory::class);
$botSetup = $factory->create(serviceConfig: $serviceConfig);

$selector = new RegisteredUpdateProcessorSelector(
    serviceConfig: $serviceConfig,
    botSetup: $botSetup,
    moduleEnablement: $app->bound(ModuleEnablementContract::class)
        ? $app->make(ModuleEnablementContract::class)
        : null,
);

new AsyncKernel($botSetup->logger)
    ->addDaemon(
        new TgPollerDaemon(
            botConfig: new TgBotConfig(token: $token),
            queue: $botSetup->queue,
            dtoClient: $botSetup->dtoClient,
            updateProcessorSelector: $selector,
            logger: $botSetup->logger,
            pollerConfig: new TgPollerConfig(
                timeout: (int) ($options['timeout'] ?? 30),
            ),
            pollerState: new PollerState(),
            processingStatistics: $botSetup->processingStatistics,
        )
    )
    ->addDaemon(
        new ProcessorUpdateDaemon(
            queue: $botSetup->queue,
            updateRouter: new UpdateRouter(
                serviceConfig: $serviceConfig,
                botSetup: $botSetup,
                errorConsumer: new ProcessingErrorConsumer(
                    registry: new ProcessingErrorRegistry()
                        ->setDefaults(new LogErrorAction(logger: $botSetup->logger)),
                ),
            ),
            logger: $botSetup->logger,
            processorScheduler: $botSetup->processorScheduler,
        )
    )
    ->run();
