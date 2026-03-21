<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use GuzzleHttp\Promise\PromiseInterface;

interface TgBotApiDTOClientContract
{
    /** @see https://core.telegram.org/bots/api */
    public function request(
        string $token,
        TgApiMethodDTOContract $dto,
    ): TgApiResponse;

    /** @see https://core.telegram.org/bots/api */
    public function requestAsync(
        string $token,
        TgApiMethodDTOContract $dto,
        int $attempt = 1,
    ): PromiseInterface;
}
