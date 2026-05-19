<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Pollers\ConfigPoller;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;

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
    'help',
    'poller::',
    'dispatcher::',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx             # Default Telegram Token

php commands/poller-daemon.php                       # async fiber-based polling (default)
php commands/poller-daemon.php --echo --show --sync  # Useful debug mode
Options:
  --help
  --token=xxx:xxx                             # use custom token
  --sync                                      # sync long-poll mode
  --async                                     # async fiber-based polling mode
  --queue                                     # use LaravelQueueDtoPipelineDispatcher
  --echo                                      # echo reply to messages
  --store                                     # store messages to database
  --log                                       # log messages to stderr
  --show                                      # dump update objects
  --token=xxx:xxx                             # use custom token
  --no-ack                                    # read without acknowledging updates
  --poller::                                  # poller selection (sync|async|custom)
  --dispatcher::                              # dispatcher selection (sync|async|queue|custom)
  --memory-limit=512M                         # PHP memory limit
  --log-level=debug|info|warning|error        # minimum log level (default: info)
";
    exit(0);
}

ini_set('memory_limit', (string)($options['memory-limit'] ?? '512M'));
function_exists('pcntl_async_signals') && pcntl_async_signals(true);

$token = getCommandToken($options);

$config = new TgUpdateExampleConfig(
    bot: new TgBotConfig(token: $token)
);
initUpdatePollerConfig($options, $config);

$logger = TgPureFactory::logger($config);
$sender = TgPureFactory::tgApiSender($config);

$poller = ConfigPoller::build(
    updateProcessor: new UpdateDTOInitProcessor(
        processorRegistry: TgPureFactory::processorRegistry(config: $config),
        logger: $logger,
    ),
    dtoClient: $sender,
    logger: $logger,
);

verifyBot($sender, $token);

$poller->run($config);
