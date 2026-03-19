<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Streaming;

use BAGArt\ASKClient\Contracts\Client\ASKClientContract;
use BAGArt\TelegramBot\ApiCommunication\AskTransport\TgOperation;

final class TgUpdateStream
{
    private bool $running = false;

    public function __construct(
        private ASKClientContract $client,
    ) {
    }

    public function listen(): iterable
    {
        $this->running = true;

        while ($this->running) {
            yield $this->client->execute(
                new TgOperation(method: 'getUpdates'),
            )->await();
        }
    }

    public function stop(): void
    {
        $this->running = false;
    }
}
