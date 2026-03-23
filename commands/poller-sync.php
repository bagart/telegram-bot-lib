<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Async\Dispatchers;
use BAGArt\TelegramBot\ApiCommunication\Pollers;
use BAGArt\TelegramBot\ApiCommunication\Pollers\SyncPoller;
use BAGArt\TelegramBot\ExampleServices\TgUpdateExampleConfig;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateDTOInitProcessor;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;

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
    'dbg',
    'help',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/poller-sync.php                # sync long-poll with DTO processor
php commands/poller-sync.php --help         # show help
php commands/poller-sync.php                # sync polling with DTOProcessor
  --echo                                    # echo reply to messages
  --store                                   # store messages to database
  --log                                     # log messages to stderr
  --show                                    # dump update objects
  --dbg                                     # dump raw Telegram response (debug)
  --token=xxx:xxx                           # use custom token
  --no-ack                                  # read without acknowledging updates
";
    exit(0);
}

$token = getCommandToken($options);

$config = new TgUpdateExampleConfig(token: $token);
initPollerConfig(
    [
        'poller' => Pollers\SyncPoller::TYPE,
        'dispatcher' => Dispatchers\SyncDtoPipelineDispatcher::TYPE,
    ] + $options,
    $config,
);

TgBotLogWrapper::$debugEnabled = $config->dbg;
$logger = TgPureFactory::logger();

$poller = new SyncPoller(
    updateProcessor: new UpdateDTOInitProcessor(
        processorRegistry: TgPureFactory::processorRegistry(
            processors: [
                'echo' => $config->echo,
                'log' => $config->log,
                'store' => $config->store,
                'show' => $config->show,
            ],
            config: $config,
        ),
        logger: $logger,
    ),
    logger: $logger,
);

verifyBot(TgPureFactory::dtoClient($config), $token);

$poller->run($config);
