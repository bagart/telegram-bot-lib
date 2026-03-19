<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;

/**
 * Typed Telegram Bot API client — accepts DTOs, returns parsed responses.
 *
 * Extends {@see WithASKTickableContract} so that the underlying HTTP transport
 * (when tickable) is driven by the async kernel through the daemon chain.
 */
interface TgBotApiDTOClientContract extends WithASKTickableContract
{
    /**
     * Send a typed DTO request synchronously.
     */
    public function request(
        TgBotConfig $botConfig,
        TgApiMethodDTOContract $dto,
        ?int $timeout = null,
    ): TgApiResponse;

    /**
     * Send a typed DTO request asynchronously.
     */
    public function requestAsync(
        TgBotConfig $botConfig,
        TgApiMethodDTOContract $dto,
        ?int $timeout = null,
    ): ASKFutureContract;
}
