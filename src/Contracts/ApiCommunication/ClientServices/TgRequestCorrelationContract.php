<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

interface TgRequestCorrelationContract
{
    public function generateRequestId(): string;

    public function generateResponseQueueByRequestId(string $requestId): string;
}
