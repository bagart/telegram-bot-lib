<?php

use BAGArt\TelegramBot\ExampleServices\TgPureFactory;

/**
 * Resolve Telegram bot token from --token option or TELEGRAM_BOT_TOKEN env.
 *
 * @param  array<string, string|bool>  $options  parsed options from parseCommandOptions()
 * @return string                              Telegram Bot API token
 *
 * Exits with code 1 if no token is found.
 */
function getCommandToken(array $options): string
{
    $token = $options['token'] ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
    if (!$token) {
        TgPureFactory::logger()->error('Token not set. Use --token=xxx:xxx or "export TELEGRAM_BOT_TOKEN=xxx:xxx"');
        exit(1);
    }

    if (!preg_match('/^\d{5,20}:[A-Za-z0-9_-]+$/', $token)) {
        TgPureFactory::logger()->error("Invalid token format. Expected: {numeric_id}:{secret}");
        exit(1);
    }
    assert(is_numeric(explode(':', $token)[0]));

    return $token;
}
