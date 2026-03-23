<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ExampleServices\TgPureFactory;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';
require_once __DIR__.'/includes/resolve-token.php';
require_once __DIR__.'/includes/verify-bot.php';

$options = parseCommandOptions([
    'token::',
    'help',
]);

if (isset($options['help'])) {
    echo "Usage:
php commands/get-me.php                   # show bot info
php commands/get-me.php --token=xxx:xxx   # use custom token
";
    exit(0);
}

$token = getCommandToken($options);
$user = verifyBot(TgPureFactory::dtoClient(), $token);

echo "\nid:       {$user->id}\n";
echo "username: @{$user->username}\n";
echo "name:     ".trim("{$user->firstName} {$user->lastName}\n");
echo "is_bot:   ".($user->isBot ? 'yes' : 'no')."\n";
