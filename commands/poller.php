<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Pollers\ConfigPoller;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';
require_once __DIR__.'/includes/resolve-token.php';
require_once __DIR__.'/includes/verify-bot.php';
require_once __DIR__.'/includes/init-config.php';

// --- Parse options ---
$options = parseCommandOptions([
    'sync',
    'async',
    'queue',
    'token::',
    'echo',
    'show',
    'store',
    'log',
    'no-ack',
    'dbg',
    'help',
    'poller::',
    'dispatcher::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/poller.php                     # async fiber-based polling (default)
php commands/poller.php --sync              # sync long-poll
php commands/poller.php --async             # async fiber-based polling
php commands/poller.php --queue             # queue-based dispatcher
php commands/poller.php --help              # show help

Options:
  --sync                                    # sync long-poll mode
  --async                                   # async fiber-based polling mode
  --queue                                   # use LaravelQueueDtoPipelineDispatcher
  --echo                                    # echo reply to messages
  --store                                   # store messages to database
  --log                                     # log messages to stderr
  --show                                    # dump update objects
  --dbg                                     # dump raw Telegram response (debug)
  --token=xxx:xxx                           # use custom token
  --no-ack                                  # read without acknowledging updates
  --poller::                                # poller selection (sync|async|custom)
  --dispatcher::                            # dispatcher selection (sync|async|queue|custom)
";
    exit(0);
}

$token = getCommandToken($options);

$config = new TgUpdateExampleConfig(token: $token);
initPollerConfig(
    $options,
    $config,
);
TgBotLogWrapper::$debugEnabled = $config->dbg;

$poller = new ConfigPoller(
    processorRegistry: TgPureFactory::processorRegistry(
        processors: [
            'echo' => $config->echo,
            'log' => $config->log,
            'store' => $config->store,
            'show' => $config->show,
        ],
        config: $config,
    ),
    logger: TgPureFactory::logger(),
);

verifyBot(TgPureFactory::dtoClient($config), $token);

$poller->run($config);
