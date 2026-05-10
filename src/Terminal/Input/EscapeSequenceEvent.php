<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Terminal\Input;

class EscapeSequenceEvent extends InputEvent
{
    public function __construct(
        public readonly string $sequence,
    ) {
    }

    public function isArrowUp(): bool
    {
        return $this->sequence === '[A';
    }

    public function isArrowDown(): bool
    {
        return $this->sequence === '[B';
    }

    public function isArrowLeft(): bool
    {
        return $this->sequence === '[D';
    }

    public function isArrowRight(): bool
    {
        return $this->sequence === '[C';
    }
}
