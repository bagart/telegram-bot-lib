<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\CLI\Chatting\Input;

class ControlKeyEvent extends InputEvent
{
    public const CTRL_U = 'U';
    public const CTRL_A = 'A';
    public const CTRL_E = 'E';
    public const CTRL_C = 'C';

    public function __construct(
        public readonly string $key,
    ) {
    }

    public function isCtrlU(): bool
    {
        return $this->key === self::CTRL_U;
    }

    public function isCtrlA(): bool
    {
        return $this->key === self::CTRL_A;
    }

    public function isCtrlE(): bool
    {
        return $this->key === self::CTRL_E;
    }

    public function isCtrlC(): bool
    {
        return $this->key === self::CTRL_C;
    }
}
