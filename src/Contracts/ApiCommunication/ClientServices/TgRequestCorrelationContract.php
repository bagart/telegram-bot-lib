<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundRequestDTO;

interface TgRequestCorrelationContract
{
    public function generateRequestId(): string;

    public function generateResponseQueue(TgOutboundRequestDTO $request): string;
}
