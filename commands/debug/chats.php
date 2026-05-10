<?php

declare(strict_types=1);

use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TgApi\Types\DTO\CallbackQueryTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatJoinRequestTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';
require_once __DIR__.'/includes/resolve-token.php';
require_once __DIR__.'/includes/verify-bot.php';

$options = parseCommandOptions([
    'token::',
    'limit::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Bot Token

php commands/debug/chats.php                # show active chats

Options:
  --help
  --limit=200                               # fetch more updates
  --token=xxx:xxx                           # use custom token
  --log-level=debug|info|warning|error      # minimum log level (default: info)
";
    exit(0);
}

$token = getCommandToken($options);
$tgDTOClient = TgPureFactory::dtoClient();
$limit = (int)($options['limit'] ?? 100);
$user = verifyBot($tgDTOClient, $token);

echo "Bot: @{$user->username} (".trim($user->firstName.' '.($user->lastName ?? '')).")\n";
echo "Fetching updates (limit={$limit})...\n\n";

$response = $tgDTOClient->request(
    $token,
    new TgApi\Methods\DTO\GetUpdatesMethodDTO(
        offset: 0,
        limit: $limit,
        timeout: 0,
        allowedUpdates: ['message', 'callback_query', 'chat_join_request'],
    ),
);

if (
    !$response->ok
    || !is_array($response->result)
) {
    echo "Failed to fetch updates.\n";
    exit(1);
}

/** @var array<string, array{chat: array, users: array<string, array>, lastAction: string, lastTime: int}> $chats */
$chats = [];

foreach ($response->result as $update) {
    assert($update instanceof UpdateTypeDTO);

    $chat = null;
    $from = null;
    $action = '';
    $time = 0;

    if ($update->message instanceof MessageTypeDTO) {
        $msg = $update->message;
        $chat = $msg->chat;
        $from = $msg->from;
        $time = $msg->date;
        $action = buildActionText('message', $msg->text ?? mediaType($msg));
    } elseif ($update->editedMessage instanceof MessageTypeDTO) {
        $msg = $update->editedMessage;
        $chat = $msg->chat;
        $from = $msg->from;
        $time = $msg->editDate ?? $msg->date;
        $action = buildActionText('edited', $msg->text ?? mediaType($msg));
    } elseif ($update->channelPost instanceof MessageTypeDTO) {
        $msg = $update->channelPost;
        $chat = $msg->chat;
        $from = $msg->from;
        $time = $msg->date;
        $action = buildActionText('channel_post', $msg->text ?? mediaType($msg));
    } elseif ($update->callbackQuery instanceof CallbackQueryTypeDTO) {
        $cb = $update->callbackQuery;
        $chat = $cb->message?->chat ?? $cb->chatInstance ? null : null;
        $from = $cb->from;
        $time = $cb->message?->date ?? 0;
        $action = "callback: {$cb->data}";
    } elseif ($update->chatJoinRequest instanceof ChatJoinRequestTypeDTO) {
        $req = $update->chatJoinRequest;
        $chat = $req->chat;
        $from = $req->from;
        $time = $req->date;
        $action = 'join_request';
    }

    if ($chat === null) {
        continue;
    }

    $chatKey = (string)$chat->id;

    if (!isset($chats[$chatKey])) {
        $chats[$chatKey] = [
            'chatId' => $chat->id,
            'chatType' => $chat->type->value,
            'chatTitle' => $chat->title ?? $chat->username ?? '-',
            'users' => [],
            'lastAction' => $action,
            'lastTime' => $time,
        ];
    }

    if ($from instanceof UserTypeDTO && !isset($chats[$chatKey]['users'][$from->id])) {
        $chats[$chatKey]['users'][$from->id] = [
            'userId' => $from->id,
            'username' => $from->username ? '@'.$from->username : '-',
            'firstName' => $from->firstName,
            'isBot' => $from->isBot ? 'yes' : 'no',
        ];
    }

    if ($time >= $chats[$chatKey]['lastTime']) {
        $chats[$chatKey]['lastAction'] = $action;
        $chats[$chatKey]['lastTime'] = $time;
    }
}

if ($chats === []) {
    echo "No active chats found.\n";
    exit(0);
}

// Sort by most recent activity
usort($chats, fn ($a, $b) => $b['lastTime'] <=> $a['lastTime']);

// === Display chats table ===
echo chats.phpstr_pad('Chat ID', 14)
    .str_pad('Type', 12)
    .str_pad('Title', 28)
    .str_pad('Users', 7)
    .str_pad('Last Action', 52)
    ."\n";
echo str_repeat('-', 113)."\n";

foreach ($chats as $c) {
    $lastTime = $c['lastTime'] > 0 ? date('H:i:s', $c['lastTime']) : '-';
    $action = mb_substr($c['lastAction'], 0, 44);
    echo chats.phpstr_pad((string)$c['chatId'], 14)
        .str_pad($c['chatType'], 12)
        .str_pad(mb_substr($c['chatTitle'], 0, 26), 28)
        .str_pad((string)count($c['users']), 7)
        .str_pad("[{$lastTime}] {$action}", 52)
        ."\n";
}

echo "\n";

// === Display users table ===
echo chats.phpstr_pad('User ID', 14)
    .str_pad('Username', 22)
    .str_pad('Name', 22)
    .str_pad('Bot?', 6)
    .str_pad('Chats', 20)
    ."\n";
echo str_repeat('-', 84)."\n";

/** @var array<string, array{user: array, chatIds: string}> $allUsers */
$allUsers = [];
foreach ($chats as $c) {
    foreach ($c['users'] as $u) {
        $key = (string)$u['userId'];
        if (!isset($allUsers[$key])) {
            $allUsers[$key] = [
                'user' => $u,
                'chatIds' => (string)$c['chatId'],
            ];
        } else {
            $allUsers[$key]['chatIds'] .= ', '.$c['chatId'];
        }
    }
}

foreach ($allUsers as $item) {
    $u = $item['user'];
    echo chats.phpstr_pad((string)$u['userId'], 14)
        .str_pad($u['username'], 22)
        .str_pad(mb_substr($u['firstName'], 0, 20), 22)
        .str_pad($u['isBot'], 6)
        .str_pad(mb_substr($item['chatIds'], 0, 18), 20)
        ."\n";
}

echo "\nTotal: ".count($chats)." chats, ".count($allUsers)." users\n";

// --- Helpers ---

function buildActionText(string $type, ?string $text): string
{
    if ($type === 'message') {
        return mb_substr($text ?? '[empty]', 0, 50);
    }

    return "[{$type}] ".mb_substr($text ?? '', 0, 40);
}

function mediaType(MessageTypeDTO $msg): string
{
    if ($msg->photo !== null) {
        return '[photo]';
    }
    if ($msg->video !== null) {
        return '[video]';
    }
    if ($msg->document !== null) {
        return '[document]';
    }
    if ($msg->sticker !== null) {
        return '[sticker]';
    }
    if ($msg->voice !== null) {
        return '[voice]';
    }
    if ($msg->audio !== null) {
        return '[audio]';
    }
    if ($msg->animation !== null) {
        return '[gif]';
    }
    if ($msg->location !== null) {
        return '[location]';
    }
    if ($msg->contact !== null) {
        return '[contact]';
    }
    if ($msg->poll !== null) {
        return '[poll]';
    }
    if ($msg->newChatMembers !== null) {
        return '[new_member]';
    }
    if ($msg->leftChatMember !== null) {
        return '[left_member]';
    }
    if ($msg->newChatTitle !== null) {
        return '[title_change]';
    }
    if ($msg->pinnedMessage !== null) {
        return '[pinned]';
    }

    return '[media]';
}
