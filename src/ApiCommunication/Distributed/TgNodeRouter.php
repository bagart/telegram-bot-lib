<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Distributed;

use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgOperation;

final class TgNodeRouter
{
    public function route(TgOperation $op): string
    {
        return match ($op->method) {
            'getUpdates' => 'poller-node',
            'sendMessage' => 'sender-node',
            default => 'default-node',
        };
    }
}
