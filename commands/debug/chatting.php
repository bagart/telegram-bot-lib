<?php

declare(strict_types=1);

use BAGArt\TelegramBot\Chat\ChatSession;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\ExampleServices\TgPureFactory;
use BAGArt\TelegramBot\Terminal\Input\ControlKeyEvent;
use BAGArt\TelegramBot\Terminal\Input\EnterPressedEvent;
use BAGArt\TelegramBot\Terminal\Input\EscapeSequenceEvent;
use BAGArt\TelegramBot\Terminal\Input\PrintableCharEvent;
use BAGArt\TelegramBot\Terminal\Input\TerminalInputHandler;
use BAGArt\TelegramBot\Terminal\TerminalUiRenderer;
use BAGArt\TelegramBot\TgApi\Methods\DTO\GetUpdatesMethodDTO;
use BAGArt\TelegramBot\TgApi\Methods\DTO\SendMessageMethodDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\UpdateTypeDTO;
use BAGArt\TelegramBot\TgBotConfig;
use BAGArt\TelegramBot\TgUpdateConfig;

require_once __DIR__.'/../../../../vendor/autoload.php';
require_once __DIR__.'/includes/validate-options.php';
require_once __DIR__.'/includes/resolve-token.php';
require_once __DIR__.'/includes/verify-bot.php';

// --- Parse options ---
$options = parseCommandOptions([
    'chat::',
    'user-id::',
    'username::',
    'token::',
    'width::',
    'help',
    'log-level::',
]);

if (isset($options['help'])) {
    echo "Usage:
export TELEGRAM_BOT_TOKEN=xxx:xxx           # Default Telegram Bot Token

php commands/debug/chatting.php --chat=123456     # Interactive chat with chat ID 123456

Options:
  --help
  --chat=ID                                 # Target chat ID (required)
  --user-id=ID                              # Filter by user ID
  --username=name                           # Filter by username (without @)
  --token=xxx:xxx                           # Use custom token
  --width=120                               # UI box width in characters (default: 120)
  --help                                    # Show help
  --log-level=debug|info|warning|error      # minimum log level (default: info)
";
    exit(0);
}

$token = getCommandToken($options);

$chatId = $options['chat'] ?? null;
if (!$chatId) {
    echo "Error: Chat ID is required. Use --chat=ID\n";
    exit(1);
}

$userId = $options['user-id'] ?? null;
$username = $options['username'] ?? null;
$width = isset($options['width']) ? (int)$options['width'] : 120;

echo "Starting active chat mode for chat: {$chatId}\n";
if ($userId) {
    echo "Filtering by user ID: {$userId}\n";
} elseif ($username) {
    echo "Filtering by username: @{$username}\n";
}
echo "Press Ctrl+C to stop. Type message and press Enter to send.\n";
echo "Shortcuts: Ctrl+U=clear, Ctrl+A=home, Ctrl+E=end, Up/Down=history\n";
echo "\n";

$config = new TgUpdateConfig(bot: new TgBotConfig(token: $token));

$dtoClient = TgPureFactory::dtoClient($config);

verifyBot($dtoClient, $token);

ob_implicit_flush(true);

// --- Setup ---
$chatSession = new ChatSession($chatId, $userId, $username);
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

// --- Main loop: input-first architecture ---
// Keyboard input is processed immediately, Telegram polling happens between input
$uiRenderer->renderChatInterface(
    $chatSession->getMessages(),
    $inputHandler->getInputBuffer(),
    $inputHandler->getCursorPos()
);

while ($running) {
    pcntl_signal_dispatch();

    // 1. Process all pending keyboard input immediately
    $needsRender = processInput($dtoClient, $token, $chatSession, $inputHandler, $running);
    if (!$running) {
        break;
    }
    if ($needsRender) {
        $uiRenderer->renderChatInterface(
            $chatSession->getMessages(),
            $inputHandler->getInputBuffer(),
            $inputHandler->getCursorPos()
        );
        continue; // Re-render done, check for more input
    }

    // 2. Check if more input is pending (user still typing)
    $read = [STDIN];
    $write = null;
    $except = null;
    $hasMoreInput = stream_select($read, $write, $except, 0, 50000); // 50ms wait

    if ($hasMoreInput > 0) {
        continue; // More input coming, skip Telegram poll
    }

    // 3. No pending input - poll Telegram (returns immediately)
    try {
        $response = $dtoClient->request(
            $token,
            new GetUpdatesMethodDTO(
                offset: $lastUpdateId > 0 ? $lastUpdateId + 1 : 0,
                limit: 10,
                timeout: 0, // return immediately
                allowedUpdates: ['message'],
            )
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
                        $inputHandler->getCursorPos()
                    );
                }
            }
        }
    } catch (\Throwable $e) {
        $errorCount++;
        if ($errorCount === 1) {
            fwrite(STDERR, "[chatting] Poll error: {$e->getMessage()}\n");
        }
        usleep(min($errorCount * 2000000, 30000000));
        continue;
    }

    // Rate-limit friendly delay: ~2s between polls
    usleep(2000000);
}

// --- Cleanup ---
$inputHandler->restoreTerminalMode();
$uiRenderer->resetTerminal();
echo "Stopped.\n";
exit(0);

/**
 * Process all pending keyboard events. Returns true if UI needs re-render.
 */
function processInput(
    TgBotApiDTOClientContract $dtoClient,
    string $token,
    ChatSession $chatSession,
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
                        $token,
                        new SendMessageMethodDTO(
                            chatId: $chatSession->getChatId(),
                            text: $text,
                        ),
                    );
                    $chatSession->addOutgoingMessage($text);
                } catch (\Throwable $e) {
                    fwrite(STDERR, "[chatting] Send error: {$e->getMessage()}\n");
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
