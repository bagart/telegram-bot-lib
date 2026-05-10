<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundRequestDTO;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundResponseDTO;

interface QueueConsumerContract
{
    public function connect(): void;

    public function consume(): ?TgOutboundRequestDTO;

    public function consumeResponseQueue(string $queueName): ?TgOutboundResponseDTO;
}
