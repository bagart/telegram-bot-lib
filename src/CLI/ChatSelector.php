<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\CLI;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\TgApi;
use BAGArt\TelegramBot\TgApi\Types\DTO\CallbackQueryTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\ChatJoinRequestTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UserTypeDTO;

final class ChatSelector
{
    /**
     * Fetch recent updates, display a chat table, and prompt user to pick one.
     *
     * @return string selected chat ID
     */
    public static function select(
        TgBotApiDTOClientContract $tgDTOClient,
        TgBotConfig $botConfig,
        int $limit = 100,
        ?ASKLogWrapper $logger = null,
    ): string {
        $logger?->info('Fetching updates for chat selector', ['limit' => $limit]);

        $response = $tgDTOClient->request(
            $botConfig,
            new TgApi\Methods\DTO\GetUpdatesMethodDTO(
                offset: 0,
                limit: $limit,
                timeout: 300,
                allowedUpdates: ['message', 'callback_query', 'chat_join_request'],
            ),
        );

        if (!$response->ok || !is_array($response->result)) {
            $logger?->error('Failed to fetch updates for chat selector');
            echo "Failed to fetch updates.\n";
            exit(1);
        }

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
                $action = self::buildActionText('message', $msg->text ?? self::mediaType($msg));
            } elseif ($update->editedMessage instanceof MessageTypeDTO) {
                $msg = $update->editedMessage;
                $chat = $msg->chat;
                $from = $msg->from;
                $time = $msg->editDate ?? $msg->date;
                $action = self::buildActionText('edited', $msg->text ?? self::mediaType($msg));
            } elseif ($update->channelPost instanceof MessageTypeDTO) {
                $msg = $update->channelPost;
                $chat = $msg->chat;
                $from = $msg->from;
                $time = $msg->date;
                $action = self::buildActionText('channel_post', $msg->text ?? self::mediaType($msg));
            } elseif ($update->callbackQuery instanceof CallbackQueryTypeDTO) {
                $cb = $update->callbackQuery;
                $chat = $cb->message?->chat;
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

            $chatKey = (string) $chat->id;

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

        usort($chats, fn ($a, $b) => $b['lastTime'] <=> $a['lastTime']);

        $indexedChats = array_values($chats);
        $logger?->debug('Chat selector found chats', ['count' => count($indexedChats)]);

        echo "=== Active Chats ===\n\n";
        echo mb_str_pad('#', 4)
            .mb_str_pad('Chat ID', 14)
            .mb_str_pad('Type', 12)
            .mb_str_pad('Title', 28)
            .mb_str_pad('Users', 7)
            .mb_str_pad('Last Action', 48)
            ."\n";
        echo str_repeat('-', 113)."\n";

        foreach ($indexedChats as $i => $c) {
            $num = $i + 1;
            $lastTime = $c['lastTime'] > 0 ? date('H:i', $c['lastTime']) : '-';
            $action = mb_substr($c['lastAction'], 0, 40);
            echo mb_str_pad((string) $num, 4)
                .mb_str_pad((string) $c['chatId'], 14)
                .mb_str_pad($c['chatType'], 12)
                .mb_str_pad(mb_substr($c['chatTitle'], 0, 26), 28)
                .mb_str_pad((string) count($c['users']), 7)
                .mb_str_pad("[{$lastTime}] {$action}", 48)
                ."\n";
        }

        echo "\n";

        $selectedIndex = null;

        while ($selectedIndex === null) {
            echo 'Select chat (1-'.count($indexedChats).') or "q" to quit: ';
            $line = trim(fgets(STDIN) ?: '');

            if ($line === 'q' || $line === 'Q') {
                echo "Bye.\n";
                exit(0);
            }

            if (!is_numeric($line)) {
                echo "Invalid input. Enter a number.\n";
                continue;
            }

            $idx = (int) $line;
            if ($idx < 1 || $idx > count($indexedChats)) {
                echo "Out of range. Enter 1-".count($indexedChats).".\n";
                continue;
            }

            $selectedIndex = $idx - 1;
        }

        $chatId = (string) $indexedChats[$selectedIndex]['chatId'];
        echo "Selected chat: {$chatId}\n\n";

        $logger?->info('Chat selected', ['chatId' => $chatId]);

        return $chatId;
    }

    private static function buildActionText(string $type, ?string $text): string
    {
        if ($type === 'message') {
            return mb_substr($text ?? '[empty]', 0, 50);
        }

        return "[{$type}] ".mb_substr($text ?? '', 0, 40);
    }

    private static function mediaType(MessageTypeDTO $msg): string
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
}
