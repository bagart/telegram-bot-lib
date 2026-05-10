<?php

declare(strict_types=1);

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\BotServices\WebhookManager;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';
require_once __DIR__.'/includes/resolve-token.php';
require_once __DIR__.'/includes/verify-bot.php';

$options = parseCommandOptions([
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

php commands/webhook.php                                     # show current webhook + auto-secret
php commands/webhook.php --url=https://example.com/tg        # set webhook with auto-generated secret
php commands/webhook.php --url=... --secret=custom-secret    # set webhook with custom secret
php commands/webhook.php --url=... --secret                  # set webhook with empty secret
php commands/webhook.php --delete                            # delete webhook

Options:
  --help
  --token=xxx:xxx                                            # use custom token
  --url=https://example.com/tg                               # set webhook (secret auto-generated)
  --secret                                                   # use webhook secret. Empty is empty secret. Not provided is auto-generated
  --log-level=debug|info|warning|error                       # minimum log level (default: info)
";
    exit(0);
}

$token = getCommandToken($options);
$secretService = new AutoSecretByTokenService();
$secret = $options['secret'] ?? $secretService->secret($token) ?: null;
verifyBot(TgPureFactory::dtoClient(), $token);

$webhookManager = new WebhookManager(
    tgDTOClient: TgPureFactory::dtoClient(),
    secretService: $secretService,
);

$webhookDTO = $webhookManager->get($token);
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
    $ok = $webhookManager->delete($token);

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
        token: $token,
        url: $url,
        secretToken: $secret,
    );

    echo ($ok ? 'OK' : 'FAILED')."\n";
    exit($ok ? 0 : 1);
}
