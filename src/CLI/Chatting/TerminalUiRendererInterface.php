<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\CLI\Chatting;

interface TerminalUiRendererInterface
{
    public function renderChatInterface(array $messages, string $inputBuffer): void;

    public function clearScreen(): void;

    public function positionCursor(int $row, int $col): void;
}
