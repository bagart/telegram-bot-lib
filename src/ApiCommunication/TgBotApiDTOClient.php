<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\TgApiServices\TgApiResponse;
use GuzzleHttp\Promise\PromiseInterface;

class TgBotApiDTOClient implements TgBotApiDTOClientContract
{
    public function __construct(
        private readonly TgBotApiClientContract $tgClient,
        private readonly TgApiDTOMapperContract $tgApiDTOMapper,
        private readonly TgBotApiReturnParser $returnParser,
    ) {
    }

    public function request(
        string $token,
        TgApiMethodDTOContract $dto,
    ): TgApiResponse {
        return $this->requestAsync($token, $dto)->wait();
    }

    /**
     * @see https://core.telegram.org/bots/api
     */
    public function requestAsync(
        string $token,
        TgApiMethodDTOContract $dto,
        int $attempt = 1,
    ): PromiseInterface {
        return $this->tgClient
            ->requestAsync(
                token: $token,
                method: $dto->tgApiEntity(),
                params: $this->tgApiDTOMapper->toArray($dto),
                attempt: $attempt,
            )
            ->then(fn (array $return) => $this->returnParser->build(
                $dto,
                $return
            ));
    }
}
