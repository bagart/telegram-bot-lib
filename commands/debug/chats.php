<?php

declare(strict_types=1);

use BAGArt\TelegramBot\CLI\ChatSelector;
use BAGArt\TelegramBot\CLI\Chatting\Input\ControlKeyEvent;
use BAGArt\TelegramBot\CLI\Chatting\Input\EnterPressedEvent;
use BAGArt\TelegramBot\CLI\Chatting\Input\EscapeSequenceEvent;
use BAGArt\TelegramBot\CLI\Chatting\Input\PrintableCharEvent;
use BAGArt\TelegramBot\CLI\Chatting\Input\TerminalInputHandler;
use BAGArt\TelegramBot\CLI\Chatting\TerminalUiRenderer;
use BAGArt\TelegramBot\CLI\Chatting\TuiChatSession;
use BAGArt\TelegramBot\CLI\CommandActions;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Configs\TgServiceConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgBotSetupFactory;

require_once __DIR__.'/../../../../../vendor/autoload.php';

// --- Parse options ---
$options = CommandActions::parseOptions(getopt('', [
    'chat::',
    'user-id::',
    'username::',
    'token::',
    'limit::',
    'width::',
    'help',
    'log-level::',
]), [
    'chat::',
    'user-id::',
    'username::',
    'token::',
    'limit::',
    'width::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
  php commands/debug/chats.php                       # list active chats, select one to chat
  php commands/debug/chats.php --chat=123456          # open chat directly
  php commands/debug/chats.php --chat=123456 --user-id=789  # filter by user

Options:
  --chat=ID              Target chat ID (if omitted, shows chat list with selector)
  --user-id=ID           Filter messages by user ID
  --username=name        Filter messages by username (without @)
  --token=xxx:xxx        Bot token (default: TELEGRAM_BOT_TOKEN env)
  --limit=200            Number of updates to fetch for chat list (default: 100)
  --width=120            UI box width in characters (default: 120)
  --log-level=debug|info|warning|error
  --help
";
    exit(0);
}

echo "\033[33mWARNING: This interactive chat uses getUpdates polling which conflicts\n"
    ."with webhook-based services processing this bot in parallel.\n"
    ."Use only for debugging.\033[0m\n\n";

$token = CommandActions::resolveToken($options);
$botConfig = new TgBotConfig(token: $token);

$config = new TgServiceConfig(
);
$factory = TgBotSetupFactory::build();
$dtoClient = $factory->getDtoClient($config);

$user = CommandActions::verifyBot($dtoClient, $token);

echo "Bot: @{$user->username} (".trim($user->firstName.' '.($user->lastName ?? '')).")\n";

$chatId = $options['chat'] ?? null;

// --- If no chat specified, show list and let user select ---
if ($chatId === null) {
    echo "Tip: use --chat=CHAT_ID to skip chat selection.\n\n";
    $limit = (int) ($options['limit'] ?? 100);
    $chatId = ChatSelector::select($dtoClient, $botConfig, $limit);
}

// --- Common options ---
$userId = $options['user-id'] ?? null;
$username = $options['username'] ?? null;
$width = isset($options['width']) ? (int) $options['width'] : 120;

echo "Chat: {$chatId}\n";
if ($userId) {
    echo "Filtering by user ID: {$userId}\n";
} elseif ($username) {
    echo "Filtering by username: @{$username}\n";
}
echo "Press Ctrl+C to stop. Type message and press Enter to send.\n";
echo "Shortcuts: Ctrl+U=clear, Ctrl+A=home, Ctrl+E=end, Up/Down=history\n\n";

// --- Setup chatting ---
$chatSession = new TuiChatSession($chatId, $userId, $username);
$inputHandler = new TerminalInputHandler();
$uiRenderer = new TerminalUiRenderer($width);

$inputHandler->enableNonBlockingMode();

$running = true;
$lastUpdateId = 0;
$errorCount = 0;

pcntl_signal(SIGINT, function () use (&$running, $uiRenderer, $inputHandler): void {
    $inputHandler->restoreTerminalMode();
    $uiRenderer->resetTerminal();
    $running = false;
});
pcntl_signal_dispatch();

// --- Main loop ---
$uiRenderer->renderChatInterface(
    $chatSession->getMessages(),
    $inputHandler->getInputBuffer(),
    $inputHandler->getCursorPos(),
);

while ($running) {
    pcntl_signal_dispatch();

    // 1. Process all pending keyboard input immediately
    $needsRender = processInput($dtoClient, $botConfig, $chatSession, $inputHandler, $running);
    if (!$running) {
        break;
    }
    if ($needsRender) {
        $uiRenderer->renderChatInterface(
            $chatSession->getMessages(),
            $inputHandler->getInputBuffer(),
            $inputHandler->getCursorPos(),
        );
        continue;
    }

    // 2. Check if more input is pending
    $read = [STDIN];
    $write = null;
    $except = null;
    $hasMoreInput = stream_select($read, $write, $except, 0, 50000);

    if ($hasMoreInput > 0) {
        continue;
    }

    // 3. Poll Telegram
    try {
        $response = $dtoClient->request(
            $botConfig,
            new GetUpdatesMethodDTO(
                offset: $lastUpdateId > 0 ? $lastUpdateId + 1 : 0,
                limit: 10,
                timeout: 300,
                allowedUpdates: ['message'],
            ),
        );

        $errorCount = 0;

        if ($response !== null && !empty($response->result)) {
            foreach ($response->result as $update) {
                assert($update instanceof UpdateTypeDTO);
                $lastUpdateId = max($lastUpdateId, $update->updateId);

                if ($chatSession->matchesFilter($update)) {
                    $chatSession->addMessage($update->message);
                    $uiRenderer->renderChatInterface(
                        $chatSession->getMessages(),
                        $inputHandler->getInputBuffer(),
                        $inputHandler->getCursorPos(),
                    );
                }
            }
        }
    } catch (\Throwable $e) {
        ++$errorCount;
        if ($errorCount === 1) {
            fwrite(STDERR, "[chats] Poll error: {$e->getMessage()}\n");
        }
        usleep(min($errorCount * 2_000_000, 30_000_000));
        continue;
    }

    usleep(2_000_000);
}

// --- Cleanup ---
$inputHandler->restoreTerminalMode();
$uiRenderer->resetTerminal();
echo "Stopped.\n";
exit(0);

// ============================================================
// Helpers
// ============================================================

function processInput(
    TgBotApiDTOClientContract $dtoClient,
    TgBotConfig $botConfig,
    TuiChatSession $chatSession,
    TerminalInputHandler $inputHandler,
    bool &$running,
): bool {
    $needsRender = false;

    while (true) {
        $event = $inputHandler->poll();
        if ($event === null) {
            break;
        }

        if ($event instanceof EnterPressedEvent) {
            $text = $inputHandler->handleEnter();
            if ($text !== null) {
                try {
                    $dtoClient->request(
                        $botConfig,
                        new SendMessageMethodDTO(
                            chatId: $chatSession->getChatId(),
                            text: $text,
                        ),
                    );
                    $chatSession->addOutgoingMessage($text);
                } catch (\Throwable $e) {
                    fwrite(STDERR, "[chats] Send error: {$e->getMessage()}\n");
                }
                $needsRender = true;
            }
        } elseif ($event instanceof EscapeSequenceEvent) {
            if ($event->isArrowUp()) {
                $needsRender = $inputHandler->handleArrowUp();
            } elseif ($event->isArrowDown()) {
                $needsRender = $inputHandler->handleArrowDown();
            } elseif ($event->isArrowLeft()) {
                $needsRender = $inputHandler->handleArrowLeft();
            } elseif ($event->isArrowRight()) {
                $needsRender = $inputHandler->handleArrowRight();
            }
        } elseif ($event instanceof ControlKeyEvent) {
            if ($event->isCtrlC()) {
                $running = false;

                return false;
            }
            if ($event->isCtrlU()) {
                $needsRender = $inputHandler->handleCtrlU();
            } elseif ($event->isCtrlA()) {
                $needsRender = $inputHandler->handleCtrlA();
            } elseif ($event->isCtrlE()) {
                $needsRender = $inputHandler->handleCtrlE();
            }
        } elseif ($event instanceof PrintableCharEvent) {
            if ($event->char === "\x7f") {
                $needsRender = $inputHandler->handleBackspace();
            } else {
                $needsRender = $inputHandler->handlePrintableChar($event->char);
            }
        }
    }

    return $needsRender;
}
