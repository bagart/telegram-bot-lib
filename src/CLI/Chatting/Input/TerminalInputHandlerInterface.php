<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\CLI\Chatting\Input;

interface TerminalInputHandlerInterface
{
    public function enableNonBlockingMode(): void;

    public function poll(): ?InputEvent;

    public function getInputBuffer(): string;

    public function setInputBuffer(string $buffer): void;

    public function getCursorPos(): int;

    public function getHistory(): array;

    public function addToHistory(string $input): void;
}
