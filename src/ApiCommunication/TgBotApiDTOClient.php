<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Http\Pure\TgResponseParser;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistryFactory;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use GuzzleHttp\Promise\PromiseInterface;

class TgBotApiDTOClient implements TgBotApiDTOClientContract
{
    public function __construct(
        private readonly TgBotApiClientContract $tgClient,
        private readonly TgApiDTOMapperContract $tgApiDTOMapper,
        private readonly TgResponseParser $returnParser,
    ) {
    }

    public static function build(
        ?TgBotCacheWrapper $cache = null,
        ?TgBotLogWrapper $logger = null,
    ): self {
        $logger = $logger ?? TgBotLogWrapper::build();
        $cache = $cache ?? TgBotCacheWrapper::build();
        $tgApiDTOMapper = new TgApiDTOMapper(
            tgApiDTORegistry: new TgEntityToDTORegistryFactory($logger)->build(),
            logger: $logger,
        );

        return new static(
            tgClient: TgBotApiClient::build($cache),
            tgApiDTOMapper: $tgApiDTOMapper,
            returnParser: new TgResponseParser(
                tgApiDTOMapper: $tgApiDTOMapper,
                logger: $logger,
            ),
        );
    }

    public function request(
        string $token,
        TgApiMethodDTOContract $dto,
    ): TgApiResponse {
        $promise = $this->requestAsync($token, $dto);
        while ($promise->getState() === PromiseInterface::PENDING) {
            $this->tgClient->tick();
        }

        return $promise->wait();
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
            ->then(fn (array $return) => $this->returnParser->parse(
                dto: $dto,
                response: $return
            ));
    }
}
