<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../../vendor/autoload.php';

$options = CommandActions::parseOptions(getopt('', [
    'token::',
    'help',
    'log-level::',
]), [
    'token::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/debug/get-me.php                   # show bot info

Options:
  --help
  --token=xxx:xxx                         # use custom token
  --log-level=debug|info|warning|error    # minimum log level (default: info)
";
    exit(0);
}

$token = CommandActions::resolveToken($options);
$botConfig = new TgBotConfig(token: $token);
echo "\ncalling...\n";
$botSetup = TgBotSetupFactory::build()->create(
    serviceConfig: new TgServiceConfig(
    )
);
$user = CommandActions::verifyBot($botSetup->dtoClient, $token);

echo "\nid:       {$user->id}\n";
echo "username: @{$user->username}\n";
echo "name:     ".trim("{$user->firstName} {$user->lastName}\n");
echo "is_bot:   ".($user->isBot ? 'yes' : 'no')."\n\n";
