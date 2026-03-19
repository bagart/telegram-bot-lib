<?php

declare(strict_types=1);

use BAGArt\TelegramBot\CLI\ChatSelector;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../../vendor/autoload.php';

$options = CommandActions::parseOptions(getopt('', [
    'chat::',
    'text::',
    'token::',
    'timeout::',
    'help',
    'log-level::',
]), [
    'chat::',
    'text::',
    'token::',
    'timeout::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Bot Token

php commands/debug/message.php --chat=123456 --text=\"Hello\"  # Direct send

Options:
  --help
  --chat=CHAT_ID                             Target chat ID (required)
  --text=\"MESSAGE\"                         Message text (required)
  --token=xxx:xxx                            Bot token (default: TELEGRAM_BOT_TOKEN env)
  --timeout=30                               Response wait timeout in seconds
  --log-level=debug|info|warning|error       # minimum log level (default: info)
";
    exit(0);
}

$token = CommandActions::resolveToken($options);
$botConfig = new TgBotConfig(token: $token);
$factory = TgBotSetupFactory::build();
$tgDTOClient = $factory->getDtoClient(new TgServiceConfig(
));

// --- Chat selection ---
$chatId = $options['chat'] ?? null;

if ($chatId === null) {
    echo "Tip: use --chat=CHAT_ID to skip chat selection.\n\n";
    $chatId = ChatSelector::select($tgDTOClient, $botConfig);
}

// --- Text input ---
$text = $options['text'] ?? null;

if ($text === null) {
    echo 'Enter message text: ';
    $text = trim(fgets(STDIN) ?: '');
}

// --- Create DTO ---
$sendMessageDTO = new SendMessageMethodDTO(
    chatId: $chatId,
    text: $text,
);

// --- Direct mode ---
$tgDTOClient = $factory->getDtoClient(new TgServiceConfig(
));

$user = CommandActions::verifyBot($tgDTOClient, $token);

echo "Sending message to chat {$chatId}...\n";

$response = $tgDTOClient->request(
    botConfig: $botConfig,
    dto: $sendMessageDTO,
);

// --- Result ---
if ($response->ok) {
    echo "\n✓ Message sent successfully.\n";

    if ($response->result instanceof MessageTypeDTO) {
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
