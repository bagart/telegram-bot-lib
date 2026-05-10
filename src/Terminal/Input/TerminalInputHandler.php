<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Terminal\Input;

class TerminalInputHandler implements TerminalInputHandlerInterface
{
    private string $inputBuffer = '';
    private int $cursorPos = 0;
    private array $inputHistory = [];
    private int $historyIndex = -1;
    private const MAX_HISTORY = 50;
    private mixed $originalMode = null;

    public function enableNonBlockingMode(): void
    {
        if (!defined('STDIN')) {
            return;
        }

        // Save original terminal mode
        $this->originalMode = shell_exec('stty -g 2>/dev/null');

        // Set raw mode: no echo, no canonical, no signal chars
        shell_exec('stty raw -echo 2>/dev/null');

        stream_set_blocking(STDIN, false);
    }

    public function restoreTerminalMode(): void
    {
        if ($this->originalMode !== null) {
            shell_exec("stty {$this->originalMode} 2>/dev/null");
        }
    }

    public function poll(): ?InputEvent
    {
        if (!defined('STDIN')) {
            return null;
        }

        $char = fread(STDIN, 1);
        if ($char === false || $char === '') {
            return null;
        }

        if ($char === "\n" || $char === "\r") {
            return new EnterPressedEvent();
        }

        if ($char === "\x1b") {
            $seq = fread(STDIN, 2);
            if ($seq !== false && $seq !== '') {
                return new EscapeSequenceEvent($seq);
            }

            return null;
        }

        if ($char === "\x15") {
            return new ControlKeyEvent(ControlKeyEvent::CTRL_U);
        }

        if ($char === "\x01") {
            return new ControlKeyEvent(ControlKeyEvent::CTRL_A);
        }

        if ($char === "\x05") {
            return new ControlKeyEvent(ControlKeyEvent::CTRL_E);
        }

        if ($char === "\x03") {
            return new ControlKeyEvent(ControlKeyEvent::CTRL_C);
        }

        if ($char === "\x7f" || $char === "\x08") {
            return new PrintableCharEvent("\x7f");
        }

        if (ord($char) >= 32) {
            return new PrintableCharEvent($char);
        }

        return null;
    }

    public function getInputBuffer(): string
    {
        return $this->inputBuffer;
    }

    public function setInputBuffer(string $buffer): void
    {
        $this->inputBuffer = $buffer;
        $this->cursorPos = strlen($buffer);
    }

    public function getCursorPos(): int
    {
        return $this->cursorPos;
    }

    public function getHistory(): array
    {
        return $this->inputHistory;
    }

    public function addToHistory(string $input): void
    {
        $this->inputHistory[] = $input;
        if (count($this->inputHistory) > self::MAX_HISTORY) {
            array_shift($this->inputHistory);
        }
    }

    public function handleEnter(): ?string
    {
        $text = trim($this->inputBuffer);
        if ($text === '') {
            return null;
        }

        $this->addToHistory($this->inputBuffer);
        $this->inputBuffer = '';
        $this->cursorPos = 0;
        $this->historyIndex = -1;

        return $text;
    }

    public function handleArrowUp(): bool
    {
        if (empty($this->inputHistory)) {
            return false;
        }

        if ($this->historyIndex < count($this->inputHistory) - 1) {
            $this->historyIndex++;
            $this->inputBuffer = $this->inputHistory[count($this->inputHistory) - 1 - $this->historyIndex];
            $this->cursorPos = strlen($this->inputBuffer);

            return true;
        }

        return false;
    }

    public function handleArrowDown(): bool
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->inputBuffer = $this->inputHistory[count($this->inputHistory) - 1 - $this->historyIndex];
            $this->cursorPos = strlen($this->inputBuffer);

            return true;
        }

        if ($this->historyIndex === 0) {
            $this->historyIndex = -1;
            $this->inputBuffer = '';
            $this->cursorPos = 0;

            return true;
        }

        return false;
    }

    public function handleArrowLeft(): bool
    {
        if ($this->cursorPos > 0) {
            $this->cursorPos--;

            return true;
        }

        return false;
    }

    public function handleArrowRight(): bool
    {
        if ($this->cursorPos < strlen($this->inputBuffer)) {
            $this->cursorPos++;

            return true;
        }

        return false;
    }

    public function handleCtrlU(): bool
    {
        $this->inputBuffer = '';
        $this->cursorPos = 0;

        return true;
    }

    public function handleCtrlA(): bool
    {
        $this->cursorPos = 0;

        return true;
    }

    public function handleCtrlE(): bool
    {
        $this->cursorPos = strlen($this->inputBuffer);

        return true;
    }

    public function handleBackspace(): bool
    {
        if ($this->cursorPos > 0) {
            $this->inputBuffer = substr($this->inputBuffer, 0, $this->cursorPos - 1)
                .substr($this->inputBuffer, $this->cursorPos);
            $this->cursorPos--;

            return true;
        }

        return false;
    }

    public function handlePrintableChar(string $char): bool
    {
        $this->inputBuffer = substr($this->inputBuffer, 0, $this->cursorPos)
            .$char
            .substr($this->inputBuffer, $this->cursorPos);
        $this->cursorPos++;

        return true;
    }
}
