<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication;

use BAGArt\TelegramBot\ApiCommunication\Queue\TgBotApiDTOQueue;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgRequestExecutionConfig;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiDTOClientContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\TgBotApiTransportContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use BAGArt\TelegramBot\Http\Pure\TgApiResponse;
use BAGArt\TelegramBot\Wrappers\TgBotCacheWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotLogWrapper;
use BAGArt\TelegramBot\Wrappers\TgBotRedisQueueWrapper;
use GuzzleHttp\Promise\PromiseInterface;

final class TgApiSender implements TgBotApiDTOClientContract
{
    public function __construct(
        private readonly TgBotApiDTOClient $client,
        private readonly TgBotApiDTOQueue $queue,
    ) {
    }

    public static function build(
        ?TgBotCacheWrapper $cache = null,
        ?TgBotLogWrapper $logger = null,
        ?TgBotApiTransportContract $transport = null,
        ?TgBotRedisQueueWrapper $redisWrapper = null,
    ): self {
        $logger ??= TgBotLogWrapper::build();
        $cache ??= TgBotCacheWrapper::build();
        $redisWrapper ??= TgBotRedisQueueWrapper::build(logger: $logger);

        return new self(
            client: TgBotApiDTOClient::build($cache, $logger, $transport),
            queue: TgBotApiDTOQueue::build($redisWrapper, $logger),
        );
    }

    public function request(
        string $token,
        TgApiMethodDTOContract $dto,
    ): TgApiResponse {
        return $this->client->request($token, $dto);
    }

    public function requestAsync(
        string $token,
        TgApiMethodDTOContract $dto,
        int $attempt = 1,
    ): PromiseInterface {
        return $this->client->requestAsync($token, $dto, $attempt);
    }

    public function queue(
        string $token,
        TgApiMethodDTOContract $dto,
        ?TgRequestExecutionConfig $config = null,
    ): string {
        return $this->queue->queue($token, $dto, $config);
    }

    public function auto(
        string $token,
        TgApiMethodDTOContract $dto,
        string $send = 'auto',
        ?TgRequestExecutionConfig $config = null,
    ): TgApiResponse|PromiseInterface|string {
        return match ($send) {
            'sync' => $this->request($token, $dto),
            'async' => $this->requestAsync($token, $dto),
            'queue' => $this->queue($token, $dto, $config),
            default => $this->requestAsync($token, $dto),
        };
    }
}
