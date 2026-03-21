<?php

declare(strict_types=1);

use BAGArt\TelegramBot\BotServices\AutoSecretByTokenService;
use BAGArt\TelegramBot\BotServices\WebhookManager;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetMeMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;

require_once __DIR__.'/../../../../vendor/autoload.php';

$options = getopt('', ['token::', 'url::', 'delete', 'secret::', 'help']);

if (isset($options['help'])) {
    echo "
Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx

php commands/webhook.php --help
php commands/webhook.php                                     # show current webhook + auto-secret
php commands/webhook.php --token=xxx:xxx                     # use token. default: export TELEGRAM_BOT_TOKEN=xxx:xxx
php commands/webhook.php --url=https://example.com/tg        # set webhook (secret auto-generated)
php commands/webhook.php --url=... --secret=custom-secret    # set url with custom secret
php commands/webhook.php --url=... --secret                  # set url with empty secret
php commands/webhook.php --delete                            # delete webhook
";
    exit(0);
}

$token = $options['token'] ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
if (!$token) {
    TgPureFactory::logger()->error('Token not set. Use --token=xxx:xxx or "export TELEGRAM_BOT_TOKEN=xxx:xxx"');
    exit(1);
}

$secretService = new AutoSecretByTokenService();
$secret = $options['secret'] ?? $secretService->secret($token) ?: null;

try {
    $response = TgPureFactory::dtoClient()->request($token, new GetMeMethodDTO());
    $user = $response->result;
    assert($user instanceof UserTypeDTO);

    echo "✅ Bot verified: @{$user->username} (".trim($user->firstName.' '.$user->lastName).");\n";
} catch (Throwable $e) {
    echo '❌ Failed to connect to Telegram: '.$e::class."{$e->getMessage()};\n";
    exit(1);
}

$webhookManager = new WebhookManager(
    tgDTOClient: TgPureFactory::dtoClient(),
    secretService: $secretService,
);

$webhookDTO = $webhookManager->get($token);
echo "
Current token: $token
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
