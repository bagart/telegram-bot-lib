<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Wrappers;

use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundRequestDTO;
use BAGArt\TelegramBot\ApiCommunication\Queue\TgOutboundResponseDTO;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueConsumerContract;
use BAGArt\TelegramBot\Contracts\ApiCommunication\QueueProducerContract;
use BAGArt\TelegramBot\Exceptions\TgBotRedisException;
use Redis;
use Throwable;

final class TgBotRedisQueueWrapper implements QueueProducerContract, QueueConsumerContract
{
    private const int DEFAULT_BLOCK_TIMEOUT = 2;

    private bool $connected = false;

    public function __construct(
        private readonly Redis $redis,
        public string $requestQueue,
        public int $blockTimeout = self::DEFAULT_BLOCK_TIMEOUT,
        public ?TgBotLogWrapper $logger = null,
    ) {
    }

    public static function build(
        ?string $requestQueue = null,
        ?int $blockTimeout = null,
        ?TgBotLogWrapper $logger = null,
    ): self {
        $redis = new Redis();

        $host = (string) env('TG_LIB_REDIS_HOST', '127.0.0.1');
        $port = (int) env('TG_LIB_REDIS_PORT', 6379);
        $timeout = (float) env('TG_LIB_REDIS_TIMEOUT', 2.0);

        $logger?->debug("Connecting to Redis at {$host}:{$port}...");

        $connected = $redis->connect($host, $port, $timeout);

        if (! $connected) {
            throw new TgBotRedisException(
                sprintf(
                    'Cannot connect to Redis at %s:%d',
                    $host,
                    $port,
                )
            );
        }

        $logger?->debug('Redis connected');

        $wrapper = new self(
            redis: $redis,
            requestQueue: $requestQueue ?: env('TG_LIB_REDIS_REQUEST_QUEUE', 'tg-outbound-requests'),
            blockTimeout: $blockTimeout ?? (int) env('TG_LIB_REDIS_BLOCK_TIMEOUT', 2),
            logger: $logger,
        );
        $wrapper->connected = true;

        return $wrapper;
    }

    public function connect(): void
    {
        if ($this->connected) {
            $this->logger?->debug('Already connected to Redis');

            return;
        }

        $this->logger?->debug('Redis not connected; connect() called on pre-connected instance');

        $this->connected = true;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function raw(): Redis
    {
        return $this->redis;
    }

    public function close(): void
    {
        if ($this->connected) {
            $this->redis->close();
            $this->connected = false;
        }
    }

    public function publish(TgOutboundRequestDTO $request): void
    {
        $payload = serialize($request);

        $this->redis->rPush($this->requestQueue, $payload);
    }

    public function publishRaw(string $payload): void
    {
        $this->redis->rPush($this->requestQueue, $payload);
    }

    public function publishResponse(TgOutboundResponseDTO $response): void
    {
        $queueName = $response->responseQueue;

        if ($queueName === null || $queueName === '') {
            return;
        }

        $payload = serialize($response);

        $this->redis->rPush($queueName, $payload);
    }

    public function consume(): ?TgOutboundRequestDTO
    {
        try {
            $result = $this->redis->blPop($this->requestQueue, $this->blockTimeout);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($result) || count($result) < 2) {
            return null;
        }

        $payload = $result[1] ?? null;

        if ($payload === null) {
            return null;
        }

        $unserialized = unserialize($payload, [
            'allowed_classes' => true,
        ]);

        return $unserialized instanceof TgOutboundRequestDTO
            ? $unserialized
            : null;
    }

    public function consumeRaw(): ?string
    {
        try {
            $result = $this->redis->blPop($this->requestQueue, $this->blockTimeout);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($result) || count($result) < 2) {
            return null;
        }

        return $result[1] ?? null;
    }

    public function consumeNonBlocking(): ?TgOutboundRequestDTO
    {
        try {
            $result = $this->redis->lPop($this->requestQueue);
        } catch (Throwable) {
            return null;
        }

        if ($result === false || $result === null) {
            return null;
        }

        $unserialized = unserialize($result, [
            'allowed_classes' => true,
        ]);

        return $unserialized instanceof TgOutboundRequestDTO
            ? $unserialized
            : null;
    }

    public function consumeRawNonBlocking(): ?string
    {
        try {
            $result = $this->redis->lPop($this->requestQueue);
        } catch (Throwable) {
            return null;
        }

        if ($result === false || $result === null) {
            return null;
        }

        return $result;
    }

    public function consumeResponseQueue(string $queueName): ?TgOutboundResponseDTO
    {
        try {
            $result = $this->redis->blPop($queueName, $this->blockTimeout);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($result) || count($result) < 2) {
            return null;
        }

        $payload = $result[1] ?? null;

        if ($payload === null) {
            return null;
        }

        $unserialized = unserialize($payload, [
            'allowed_classes' => true,
        ]);

        return $unserialized instanceof TgOutboundResponseDTO
            ? $unserialized
            : null;
    }
}
