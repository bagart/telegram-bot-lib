<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;
use BAGArt\TelegramBot\ApiCommunication\Pollers;
use BAGArt\TelegramBot\ApiCommunication\Pollers\AsyncPoller;
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
    'token::',
    'echo',
    'show',
    'store',
    'log',
    'no-ack',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/pollers/poller-async.php               # async fiber-based updates polling
php commands/pollers/poller-async.php --help        # show help
php commands/pollers/poller-async.php               # async polling with DTOProcessor
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Bot Token


  --echo                                    # echo reply to messages
  --store                                   # store messages to database
  --log                                     # log messages to stderr
  --show                                    # dump update objects
  --token=xxx:xxx                           # use custom token
  --no-ack                                  # read without acknowledging updates
  --log-level=debug|info|warning|error      # minimum log level (default: info)
";
    exit(0);
}

$token = getCommandToken($options);

$config = new TgUpdateExampleConfig(
    bot: new TgBotConfig(token: $token)
);
initUpdatePollerConfig(
    [
        'poller' => Pollers\AsyncPoller::TYPE,
        'dispatcher' => Dispatchers\AsyncFiberDtoPipelineDispatcher::TYPE,
    ] + $options,
    $config,
);

$logger = TgPureFactory::logger($config);

$poller = AsyncPoller::build(
    updateProcessor: new UpdateDTOInitProcessor(
        processorRegistry: TgPureFactory::processorRegistry(config: $config),
        logger: $logger,
    ),
    logger: $logger,
);

verifyBot(TgPureFactory::dtoClient($config), $token);

$poller->run($config);
