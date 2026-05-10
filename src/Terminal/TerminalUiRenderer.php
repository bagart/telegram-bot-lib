<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Terminal;

final class TerminalUiRenderer implements TerminalUiRendererInterface
{
    private const int MAX_MESSAGES = 20;
    private const int MIN_BOX_WIDTH = 50;
    private const int MAX_BOX_WIDTH = 300;

    /**
     * Фиксированная ширина UI.
     * Она не зависит от resize терминала.
     */
    private int $boxWidth;

    /**
     * Фиксированная левая позиция UI.
     * После первого рендера больше не пересчитывается.
     */
    private int $leftOffset = 1;

    private bool $initialized = false;

    public function __construct(int $width = 120)
    {
        $this->boxWidth = max(
            self::MIN_BOX_WIDTH,
            min($width, self::MAX_BOX_WIDTH)
        );
    }

    public function renderChatInterface(
        array $messages,
        string $inputBuffer,
        int $cursorPos = 0
    ): void {
        $this->hideCursor();

        if (!$this->initialized) {
            $this->initializeFixedLayout();
            $this->initialized = true;
        }

        $preparedLines = $this->prepareMessageLines($messages);

        $this->renderMessages($preparedLines);
        $this->renderInputArea($inputBuffer, $cursorPos);
        $this->restoreCursorToInput($inputBuffer, $cursorPos);

        fflush(STDOUT);
    }

    public function clearScreen(): void
    {
        echo "\033[2J\033[H";
    }

    public function resetTerminal(): void
    {
        echo "\033[0m";
        echo "\033[?25h";
        echo "\033[2J\033[H";

        $this->initialized = false;
        $this->leftOffset = 1;

        fflush(STDOUT);
    }

    public function positionCursor(int $row, int $col): void
    {
        echo sprintf(
            "\033[%d;%dH",
            $row,
            $col
        );
    }

    private function initializeFixedLayout(): void
    {
        $this->clearScreen();

        /**
         * Ключевой фикс:
         * leftOffset вычисляется ОДИН раз.
         * После resize терминала не меняется.
         *
         * UI не "прыгает".
         */
        $this->leftOffset = $this->detectInitialLeftOffset();

        $this->renderStaticFrame();
    }

    private function detectInitialLeftOffset(): int
    {
        /**
         * Можно оставить всегда 1 (самый стабильный вариант)
         * либо попробовать центрировать один раз.
         *
         * Здесь intentionally fixed.
         */
        return 1;
    }

    private function renderStaticFrame(): void
    {
        $this->renderBorder();

        $title = ' Telegram Chat Active Mode ';

        $line = $this->mbStrPad(
            $title,
            $this->getInnerWidth(),
            ' ',
            STR_PAD_BOTH
        );

        $this->printAt(
            2,
            $this->leftOffset,
            "\033[36m|\033[0m"
            . $line
            . "\033[36m|\033[0m"
        );

        $this->renderBorderAtRow(3);

        for ($i = 0; $i < self::MAX_MESSAGES; $i++) {
            $row = 4 + $i;

            $this->printAt(
                $row,
                $this->leftOffset,
                $this->buildContentLine('')
            );
        }

        $dividerRow = 4 + self::MAX_MESSAGES;
        $inputRow = $dividerRow + 1;
        $footerRow = $dividerRow + 2;

        $this->renderBorderAtRow($dividerRow);
        $this->printAt(
            $inputRow,
            $this->leftOffset,
            $this->buildInputLine('')
        );
        $this->renderBorderAtRow($footerRow);
    }

    private function renderMessages(array $lines): void
    {
        $startRow = 4;

        foreach ($lines as $index => $line) {
            $row = $startRow + $index;

            $this->printAt(
                $row,
                $this->leftOffset,
                $this->buildContentLine($line)
            );
        }
    }

    private function renderInputArea(
        string $inputBuffer,
        int $cursorPos
    ): void {
        $visibleWidth = $this->getContentWidth() - 2;

        $visible = $this->getVisibleInputSegment(
            $inputBuffer,
            $cursorPos,
            $visibleWidth
        );

        $row = 3 + self::MAX_MESSAGES + 2;

        $this->printAt(
            $row,
            $this->leftOffset,
            $this->buildInputLine($visible)
        );
    }

    private function restoreCursorToInput(
        string $inputBuffer,
        int $cursorPos
    ): void {
        $visibleWidth = $this->getContentWidth() - 2;

        $bufferLength = mb_strlen(
            $inputBuffer,
            'UTF-8'
        );

        $start = 0;

        if ($bufferLength > $visibleWidth) {
            $half = (int) floor($visibleWidth / 2);

            $start = max(
                0,
                min(
                    $cursorPos - $half,
                    $bufferLength - $visibleWidth
                )
            );
        }

        $visibleCursor = max(
            0,
            min(
                $cursorPos - $start,
                $visibleWidth
            )
        );

        $row = 3 + self::MAX_MESSAGES + 2;

        /**
         * leftOffset + "| > "
         */
        $col = $this->leftOffset + 4 + $visibleCursor;

        $this->positionCursor($row, $col);
        $this->showCursor();
    }

    private function renderBorder(): void
    {
        $this->renderBorderAtRow(1);
    }

    private function renderBorderAtRow(int $row): void
    {
        $this->printAt(
            $row,
            $this->leftOffset,
            "\033[36m+"
            . str_repeat('-', $this->getInnerWidth())
            . "+\033[0m"
        );
    }

    private function printAt(
        int $row,
        int $col,
        string $content
    ): void {
        $this->positionCursor($row, $col);

        /**
         * Важно:
         * очищаем строку до конца,
         * чтобы после resize не оставались артефакты.
         */
        echo "\033[2K";
        echo $content;
    }

    private function buildContentLine(string $text): string
    {
        $text = mb_substr(
            $text,
            0,
            $this->getContentWidth(),
            'UTF-8'
        );

        $text = $this->mbStrPad(
            $text,
            $this->getContentWidth()
        );

        return
            "\033[36m|\033[0m "
            . $text
            . " \033[36m|\033[0m";
    }

    private function buildInputLine(string $text): string
    {
        $text = mb_substr(
            $text,
            0,
            $this->getContentWidth() - 2,
            'UTF-8'
        );

        $text = $this->mbStrPad(
            $text,
            $this->getContentWidth() - 2
        );

        return
            "\033[36m|\033[0m "
            . "\033[33m>\033[0m "
            . $text
            . " \033[36m|\033[0m";
    }

    private function prepareMessageLines(array $messages): array
    {
        $allLines = [];

        foreach ($messages as $msg) {
            $raw = sprintf(
                '[%s] #%d @%s: %s',
                (string) ($msg['time'] ?? ''),
                (int) ($msg['id'] ?? 0),
                (string) ($msg['from'] ?? 'unknown'),
                $this->normalizeText(
                    (string) ($msg['text'] ?? '')
                )
            );

            $wrapped = $this->wrapText(
                $raw,
                $this->getContentWidth()
            );

            foreach ($wrapped as $line) {
                $allLines[] = $line;
            }
        }

        $allLines = array_slice(
            $allLines,
            -self::MAX_MESSAGES
        );

        while (count($allLines) < self::MAX_MESSAGES) {
            array_unshift($allLines, '');
        }

        return $allLines;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(
            ["\r\n", "\r", "\n", "\t"],
            [' ', ' ', ' ', ' '],
            $text
        );

        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function wrapText(string $text, int $width): array
    {
        if ($text === '') {
            return [''];
        }

        $words = preg_split('/\s+/u', $text) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === ''
                ? $word
                : $current . ' ' . $word;

            if (mb_strlen($candidate, 'UTF-8') <= $width) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            while (mb_strlen($word, 'UTF-8') > $width) {
                $lines[] = mb_substr(
                    $word,
                    0,
                    $width,
                    'UTF-8'
                );

                $word = mb_substr(
                    $word,
                    $width,
                    null,
                    'UTF-8'
                );
            }

            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private function hideCursor(): void
    {
        echo "\033[?25l";
    }

    private function showCursor(): void
    {
        echo "\033[?25h";
    }

    private function getVisibleInputSegment(
        string $buffer,
        int $cursorPos,
        int $maxWidth
    ): string {
        $len = mb_strlen($buffer, 'UTF-8');

        if ($len <= $maxWidth) {
            return $this->mbStrPad(
                $buffer,
                $maxWidth
            );
        }

        $half = (int) floor($maxWidth / 2);

        $start = max(
            0,
            min(
                $cursorPos - $half,
                $len - $maxWidth
            )
        );

        $segment = mb_substr(
            $buffer,
            $start,
            $maxWidth,
            'UTF-8'
        );

        return $this->mbStrPad(
            $segment,
            $maxWidth
        );
    }

    private function getInnerWidth(): int
    {
        return $this->boxWidth - 2;
    }

    private function getContentWidth(): int
    {
        return $this->boxWidth - 4;
    }

    private function mbStrPad(
        string $string,
        int $length,
        string $padString = ' ',
        int $padType = STR_PAD_RIGHT
    ): string {
        $currentLength = mb_strlen(
            $string,
            'UTF-8'
        );

        if ($currentLength >= $length) {
            return mb_substr(
                $string,
                0,
                $length,
                'UTF-8'
            );
        }

        $padLength = $length - $currentLength;

        return match ($padType) {
            STR_PAD_LEFT => str_repeat(
                $padString,
                $padLength
            ) . $string,

            STR_PAD_BOTH => str_repeat(
                $padString,
                (int) floor($padLength / 2)
            )
                . $string .
                str_repeat(
                    $padString,
                    (int) ceil($padLength / 2)
                ),

            default => $string . str_repeat(
                $padString,
                $padLength
            ),
        };
    }
}
