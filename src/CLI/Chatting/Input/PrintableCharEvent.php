<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\CLI\Chatting\Input;

class PrintableCharEvent extends InputEvent
{
    public function __construct(
        public readonly string $char,
    ) {
    }
}
