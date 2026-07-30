<?php

declare(strict_types=1);

use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\TgBotSetupFactory;
use BAGArt\TelegramBot\TgIntegration\AutoSecretByTokenService;
use BAGArt\TelegramBot\TgIntegration\WebhookManager;

require_once __DIR__.'/../../../../vendor/autoload.php';

$options = CommandActions::parseOptions(getopt('', [
    'token::',
    'url::',
    'delete',
    'secret::',
    'help',
    'log-level::',
]), [
    'token::',
    'url::',
    'delete',
    'secret::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "
Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx

php commands/tg_webhook-manager.php                                     # show current tg_webhook + auto-secret
php commands/tg_webhook-manager.php --help                              # showe help
php commands/tg_webhook-manager.php --url=https://example.com/tg        # set tg_webhook with auto-generated secret
php commands/tg_webhook-manager.php --url=... --secret=custom-secret    # set tg_webhook with custom secret
php commands/tg_webhook-manager.php --url=... --secret                  # set tg_webhook with empty secret
php commands/tg_webhook-manager.php --delete                            # delete tg_webhook

Options:
  --url=https://example.com/tg                                       # set tg_webhook (secret auto-generated)
  --secret                                                           # use tg_webhook secret. Empty is empty secret. Not provided is auto-generated

  --token=xxx:xxx                                                    # use custom token
  --log-level=debug|info|warning|error                               # minimum log level (default: info)
  --help
";
    exit(0);
}

$token = CommandActions::resolveToken($options);
$botConfig = new TgBotConfig(token: $token);
$secretService = new AutoSecretByTokenService();
$secret = $options['secret'] ?? $secretService->secret($token) ?: null;
$botSetup = TgBotSetupFactory::build()->create(
    serviceConfig: new TgServiceConfig()
);
CommandActions::verifyBot($botSetup->dtoClient, $token);

$webhookManager = new WebhookManager(
    tgDTOClient: $botSetup->dtoClient,
    secretService: $secretService,
);

$webhookDTO = $webhookManager->get($botConfig);
echo "\nCurrent token: $token
{$webhookManager->buildTextInfo($webhookDTO)}

=== Secret (Auth: Telegram => Webhook) ===
  X-Telegram-Bot-Api-Secret-Token: $secret\n";

if (isset($options['delete']) && !empty($options['url'])) {
    if ($webhookDTO->url) {
        echo "\nWebhook not set to delete\n";
        exit(1);
    }

    echo "\n=== Deleting Webhook ===\n";
    $ok = $webhookManager->delete($botConfig);

    echo ($ok ? 'OK' : 'FAILED')."\n";
    exit($ok ? 0 : 1);
}

if (isset($options['url'])) {
    $url = $options['url'];

    if (empty($url)) {
        echo "\n--url requires; Use --delete to delete Webhook\n";
        exit(1);
    }

    echo "\n=== Webhook Set: ===\n";
    echo "URL:    $url\n";
    echo "Secret: $secret\n";

    $ok = $webhookManager->set(
        botConfig: $botConfig,
        url: $url,
        secretToken: $secret,
    );

    echo ($ok ? 'OK' : 'FAILED')."\n";
    exit($ok ? 0 : 1);
}
