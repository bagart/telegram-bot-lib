<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Clients;

use BAGArt\ASKClient\Contracts\ASKFutureContract;
use BAGArt\AsyncKernel\Contracts\Daemons\WithASKTickableContract;
use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Contracts\TgApiServices\TgApiDTOMapperContract;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Http\Pure\TgResponseParser;
use BAGArt\TelegramBot\TgApiServices\TgApiDTOMapper;
use BAGArt\TelegramBot\TgApiServices\TgEntityToDTORegistry;

/**
 * Typed Telegram Bot API client — accepts DTOs, returns parsed responses.
 */
final class TgBotApiDTOClient implements TgBotApiDTOClientContract
{
    public function __construct(
        private readonly TgBotApiClientContract $tgClient,
        private readonly TgApiDTOMapperContract $tgApiDTOMapper,
        private readonly TgResponseParser $returnParser,
        private readonly ?TgBotApiTransportContract $transport = null,
    ) {
    }

    public static function build(
        TgBotApiTransportContract $transport,
        ?ASKLogWrapper $logger = null,
    ): self {
        $tgClient = new TgBotApiClient($transport);
        $dtoMapper = new TgApiDTOMapper(
            TgEntityToDTORegistry::build(logger: $logger),
            $logger,
        );

        return new self(
            $tgClient,
            $dtoMapper,
            new TgResponseParser($dtoMapper, $logger),
            $transport,
        );
    }

    /**
     * Delegates to the underlying transport, which exposes the
     * network client's tickables when applicable.
     */
    public function tickable(): array
    {
        if ($this->transport instanceof WithASKTickableContract) {
            return $this->transport->tickable();
        }

        return [];
    }

    public function request(
        TgBotConfig $botConfig,
        TgApiMethodDTOContract $dto,
        ?int $timeout = null,
    ): TgApiResponse {
        return $this->requestAsync(
            botConfig: $botConfig,
            dto: $dto,
            timeout: $timeout,
        )->await();
    }

    public function requestAsync(
        TgBotConfig $botConfig,
        TgApiMethodDTOContract $dto,
        ?int $timeout = null,
    ): ASKFutureContract {
        $tgMethodName = $dto::tgApiEntity()->name;

        $rawFuture = $this->tgClient->requestAsync(
            $botConfig,
            $tgMethodName,
            $this->tgApiDTOMapper->toArray($dto),
            $timeout,
        );

        return $rawFuture->then(
            fn (array $response): TgApiResponse => $this->returnParser->parse(dto: $dto, response: $response),
        );
    }
}
