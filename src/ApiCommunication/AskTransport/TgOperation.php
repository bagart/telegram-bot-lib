<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\AskTransport;

final class TgOperation
{
    public function __construct(
        public string $method,
        public ?object $dto = null,
        public array $params = [],
        public array $meta = [],
    ) {
    }
}
