<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Exceptions\TgApiReturnException;
use BAGArt\TelegramBot\BotServices\BotSecretDTO;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApiServices\TgEntityNamer;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessageEchoProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\MessagePdoStoreProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\TgUpdateProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\Processors\UpdateLoggerProcessor;
use BAGArt\TelegramBot\TypeDTOProcessor\TypeDTOProcessorRegistry;

require_once __DIR__.'/../../../../vendor/autoload.php';

$options = getopt('', ['token::', 'echo', 'show', 'store', 'log', 'help']);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/poller.php                     # receive updates
php commands/poller.php --help              # show help
php commands/poller.php                     # receive updates with DTOProcessor
  --echo                                    # echo reply to messages
  --store                                   # store messages to database
  --log                                     # log messages to stderr
  --show                                    # dump update objects
  --token=xxx:xxx                           # use custom token
";
    exit(0);
}

$token = $options['token'] ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
if (!$token) {
    TgPureFactory::logger()->error('Token not set. Use --token=xxx:xxx or "export TELEGRAM_BOT_TOKEN=xxx:xxx"');
    exit(1);
}

$tgDTOClient = TgPureFactory::dtoClient();
$show = array_key_exists('show', $options);
$echo = array_key_exists('echo', $options);
$store = array_key_exists('store', $options);
$log = array_key_exists('log', $options);

$registry = new TypeDTOProcessorRegistry();
$processor = new TgUpdateProcessor($registry);

if ($echo) {
    $registry->register(
        MessageTypeDTO::class,
        new MessageEchoProcessor(
            dtoClient: $tgDTOClient,
            logger: TgPureFactory::logger(),
            token: $token,
        )
    );
}

if ($log) {
    $registry->register(
        MessageTypeDTO::class,
        new UpdateLoggerProcessor(
            logger: TgPureFactory::logger(),
            namer: new TgEntityNamer(),
        )
    );
}

if ($store) {
    $registry->register(
        MessageTypeDTO::class,
        new MessagePdoStoreProcessor(
            pdo: TgPureFactory::pdo(),
        )
    );
}

$botDTO = new BotSecretDTO(token: $token);
$botId = $botDTO->botId();

try {
    $response = $tgDTOClient->request($token, new TgApi\Methods\DTO\GetMeMethodDTO());
    $user = $response->result;
    assert($user instanceof TgApi\Types\DTO\UserTypeDTO);

    echo "✅ Bot verified: @{$user->username} (".trim($user->firstName.' '.$user->lastName).");\n";
} catch (Throwable $e) {
    echo '❌ Failed to connect to Telegram: '.$e::class."{$e->getMessage()};\n";
    exit(1);
}

echo "=== Long Poller Mode (with DTO) ===\n";
echo "Starting long poller. Press Ctrl+C to stop.\n";
echo "echo=$echo store=$store show=$show\n\n";

$offset = 0;
while (true) {
    try {
        $response = $tgDTOClient->request(
            $token,
            new TgApi\Methods\DTO\GetUpdatesMethodDTO(
                offset: $offset,
                limit: 100,
                timeout: 60,
                allowedUpdates: ['message', 'callback_query'],
            ),
        );

        if ($response->ok) {
            foreach ($response->result as $updateDTO) {
                assert($updateDTO instanceof TgApi\Types\DTO\UpdateTypeDTO);
                $offset = max($offset, $updateDTO->updateId + 1);

                if ($show) {
                    if (isset($updateDTO->message?->text)) {
                        echo "\n{$updateDTO->message->from?->firstName}: {$updateDTO->message->text}";
                    } else {
                        var_dump($updateDTO);
                    }
                } else {
                    echo '+';
                }
                $processor->process($updateDTO, $botId);
            }
            if ($show && $response->result) {
                echo "\n";
            }
        } else {
            TgPureFactory::logger()->error(
                "tg api getUpdates response not ok: ".json_encode($response, JSON_PRETTY_PRINT)
            );
            echo '?';
        }
    } catch (TgApiReturnException $e) {
        TgPureFactory::logger()->error(
            "tg api getUpdates response ".$e::class.": ".$e->getMessage()
        );
        echo '*';
    }
}
