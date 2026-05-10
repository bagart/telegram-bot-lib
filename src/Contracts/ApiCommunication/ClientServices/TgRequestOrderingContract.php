<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundRequestDTO;

interface TgRequestOrderingContract
{
    public function shouldWait(TgOutboundRequestDTO $request): bool;

    public function acquire(TgOutboundRequestDTO $request): bool;

    public function release(TgOutboundRequestDTO $request): void;
}
