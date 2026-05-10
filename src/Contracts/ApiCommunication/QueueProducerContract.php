<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundRequestDTO;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundResponseDTO;

interface QueueProducerContract
{
    public function connect(): void;

    public function publish(TgOutboundRequestDTO $request): void;

    public function publishResponse(TgOutboundResponseDTO $response): void;
}
