<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\TgBotApiClient;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiReturnException;

require_once __DIR__.'/../../../../vendor/autoload.php';

$options = getopt('', ['token::', 'echo', 'show', 'help', 'log-level::']);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Token

php commands/pollers/poller-raw.php --help          # show help
php commands/pollers/poller-raw.php                 # receive updates in raw mode
  --echo                                    # echo reply to messages
  --show                                    # dump update objects
  --token=xxx:xxx                           # use custom token
  --log-level=debug|info|warning|error      # minimum log level (default: info)
";
    exit(0);
}

$token = $options['token'] ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
if (!$token) {
    TgPureFactory::logger()->error('Token not set. Use --token=xxx:xxx or "export TELEGRAM_BOT_TOKEN=xxx:xxx"');
    exit(1);
}
$rawClient = TgBotApiClient::build(TgPureFactory::cache());
$show = array_key_exists('show', $options);
$echo = array_key_exists('echo', $options);

try {
    $response = $rawClient->requestAsync(
        token: $token,
        tgMethod: 'getMe',
        attempt: 1,
    )
        ->wait();
    $username = $response['result']['username'] ?? null;
    if (empty($response['ok']) || !$username) {
        echo "❌ Telegram Response with Error: \n".json_encode($response, JSON_PRETTY_PRINT)."\n";
        exit(1);
    }
    $name = trim(
        ($response['result']['first_name'] ?? null)
        .' '
        .($response['result']['last_name'] ?? null)
    );
    if ($name !== '') {
        $name = " ($name)";
    }
    echo "✅ Bot verified: @$username$name;\n";
} catch (Throwable $e) {
    echo '❌ Failed to connect to Telegram: '.$e::class."{$e->getMessage()};\n";
    exit(1);
}


echo "=== Long Poller Mode (raw mode) ===\n";
echo "Starting long poller. Press Ctrl+C to stop.\n\n";

$offset = 0;
while (true) {
    try {
        $response = $rawClient->requestAsync(
            token: $token,
            tgMethod: 'getUpdates',
            params: [
                'allowed_updates' => ['message', 'callback_query'],
                'limit' => 100,
                'timeout' => 60,
                'offset' => $offset,
            ],
            attempt: 1,
        )
            ->wait();

        if ($response['ok'] ?? null) {
            foreach ($response['result'] ?? [] as $update) {
                $offset = max($offset, $update['update_id'] + 1);

                if ($show) {
                    if (isset($update['message']['text'])) {
                        $name = !empty($update['message']['from'])
                            ? trim(
                                "@{$update['message']['from']['username']} "
                                ."({$update['message']['from']['first_name']} {$update['message']['from']['last_name']})"
                            )
                            : 'Unknown format';
                        echo "\n{$name}: {$update['message']['text']}";
                    } else {
                        var_dump($update);
                    }
                } else {
                    echo '+';
                }

                if ($echo && isset($update['message']['text'])) {
                    try {
                        $sendMessageResponse = $rawClient->requestAsync(
                            token: $token,
                            tgMethod: 'sendMessage',
                            params: [
                                'chat_id' => $update['message']['chat']['id'],
                                'text' => "echo: {$update['message']['text']}",
                            ],
                            attempt: 1,
                        )
                            ->wait();

                        if (!$sendMessageResponse['ok'] ?? null) {
                            TgPureFactory::logger()->error(
                                "tg api sendMessage response:".json_encode(
                                    $sendMessageResponse,
                                    JSON_PRETTY_PRINT
                                )
                            );
                            echo '-';
                        }
                    } catch (TgApiReturnException $e) {
                        TgPureFactory::logger()->error(
                            "tg api sendMessage response ".$e::class.": ".$e->getMessage()
                        );
                        echo '!';
                    }
                }
            }
            if ($show && !empty($response['result'])) {
                echo "\n";
            }
        } else {
            TgPureFactory::logger()->error(
                "tg api getUpdates response not ok:".json_encode($response, JSON_PRETTY_PRINT)
            );
            echo '?';
        }
    } catch (TgApiReturnException $e) {
        TgPureFactory::logger()->error(
            "tg api getUpdates response ".$e::class.": ".$e->getMessage()
        );
        echo '*';
    }

    echo '.';
}
