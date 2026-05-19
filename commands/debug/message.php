<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ApiCommunication\Queue\QueuedTgBotApiDTOClient;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgRequestCorrelation;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgRequestExecutionConfig;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\Wrappers\TgBotRedisQueueWrapper;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';
require_once __DIR__.'/includes/resolve-token.php';
require_once __DIR__.'/includes/verify-bot.php';

$options = parseCommandOptions([
    'chat::',
    'text::',
    'token::',
    'queue',
    'async',
    'redis-host::',
    'redis-port::',
    'ordered',
    'ordering-key::',
    'timeout::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Bot Token

php commands/debug/message.php --chat=123456 --text=\"Hello\"  # Direct send (default)

Options:
  --help
  --chat=CHAT_ID                             Target chat ID (required)
  --text=\"MESSAGE\"                         Message text (required)
  --token=xxx:xxx                            Bot token (default: TELEGRAM_BOT_TOKEN env)
  --queue                                    Use outbound queue (requires running daemon)
  --async                                    Fire-and-forget (only with --queue)
  --redis-host=127.0.0.1                     Redis host (for --queue mode)
  --redis-port=6379                          Redis port (for --queue mode)
  --ordered                                  Strict ordering (only with --queue)
  --ordering-key=bot:{token}                 Ordering scope (chat:{id}, bot:{token}, global)
  --timeout=30                               Response wait timeout in seconds
  --log-level=debug|info|warning|error       # minimum log level (default: info)
";
    exit(0);
}

// --- Required arguments ---
$chatId = $options['chat'] ?? null;
$text = $options['text'] ?? null;

if ($chatId === null || $text === null) {
    echo "Error: --chat and --text are required.\n";
    exit(1);
}

$chatId = (string)$chatId;
$text = (string)$text;
$token = getCommandToken($options);

// --- Create DTO ---
$sendMessageDTO = new SendMessageMethodDTO(
    chatId: $chatId,
    text: $text,
);

// --- Queue mode? ---
if (isset($options['queue'])) {
    // --- Use queued client ---
    $queue = TgBotRedisQueueWrapper::build(
        requestQueue: 'tg-outbound-requests',
    );

    $dtoMapper = TgPureFactory::dtoMapper();

    $executionConfig = new TgRequestExecutionConfig(
        mode: isset($options['async'])
            ? TgRequestExecutionConfig::MODE_ASYNC
            : TgRequestExecutionConfig::MODE_SYNC,
        ordered: isset($options['ordered']),
        orderingKey: $options['ordering-key'] ?? null,
        timeoutSeconds: (int)($options['timeout'] ?? 30),
    );

    $queuedClient = new QueuedTgBotApiDTOClient(
        producer: $queue,
        responseConsumer: $queue,
        correlation: new TgRequestCorrelation(
            instanceId: 'tg-message',
        ),
        defaultConfig: $executionConfig,
        logger: TgPureFactory::logger(),
    );

    echo "Sending via queue (mode={$executionConfig->mode})...\n";

    $response = $queuedClient->request(
        token: $token,
        dto: $sendMessageDTO,
    );
} else {
    // --- Direct mode (existing API) ---
    $tgDTOClient = TgPureFactory::dtoClient();

    $user = verifyBot($tgDTOClient, $token);

    echo "Sending message to chat {$chatId}...\n";

    $response = $tgDTOClient->request(
        token: $token,
        dto: $sendMessageDTO,
    );
}

// --- Result ---
if ($response->ok) {
    echo "\n✓ Message sent successfully.\n";

    if ($response->result instanceof \BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO) {
        $msg = $response->result;
        echo "  Message ID: {$msg->messageId}\n";
        echo "  Chat ID:    {$msg->chat->id}\n";
        echo "  Date:       ".date('Y-m-d H:i:s', $msg->date)."\n";

        if ($msg->from !== null) {
            echo "  From:       @{$msg->from->username} ({$msg->from->firstName})\n";
        }
    } else {
        echo "  Result: ".json_encode($response->result, JSON_UNESCAPED_UNICODE)."\n";
    }

    exit(0);
}

echo "\n✗ Message failed.\n";
echo "  Error code: ".($response->errorCode ?? 'N/A')."\n";

if (!$response->ok && $response->result === null) {
    echo "  Error: Request failed (no response from daemon)\n";
}

exit(1);
