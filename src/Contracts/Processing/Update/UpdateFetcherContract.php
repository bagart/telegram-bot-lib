<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Processing\Update;

use BAGArt\AsyncKernel\Contracts\ASKPromiseContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiCommunicationException;
use BAGArt\TelegramBot\Exceptions\ApiCommunication\TgApiRateLimitException;

interface UpdateFetcherContract
{
    /**
     * @throws TgApiCommunicationException
     * @throws TgApiRateLimitException
     */
    public function fetch(
        TgBotConfig $botConfig,
        int $offset,
        ?int $limit = null,
    ): ASKPromiseContract;
}
