<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Configs;

use BAGArt\TelegramBot\Contracts\Queue\QueueConfigContract;

final readonly class RedisQueueConfig implements QueueConfigContract
{
    public function __construct(
        private string $host = '127.0.0.1',
        private int $port = 6379,
        private float $timeout = 2.0,
        private string $prefix = 'tg:',
        private string $outboundQueue = 'tg-outbound-requests',
        private string $processorQueue = 'tg-processor-jobs',
        private int $blockTimeout = 2,
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            host: (string)getenv('TG_LIB_REDIS_HOST') ?: '127.0.0.1',
            port: (int)getenv('TG_LIB_REDIS_PORT') ?: 6379,
            timeout: (float)getenv('TG_LIB_REDIS_TIMEOUT') ?: 2.0,
            prefix: (string)getenv('TG_LIB_REDIS_PREFIX') ?: 'tg:',
            outboundQueue: (string)getenv('TG_LIB_REDIS_OUTBOUND_QUEUE') ?: 'tg-outbound-requests',
            processorQueue: (string)getenv('TG_LIB_REDIS_PROCESSOR_QUEUE') ?: 'tg-processor-jobs',
            blockTimeout: (int)getenv('TG_LIB_REDIS_BLOCK_TIMEOUT') ?: 2,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            host: (string) ($data['host'] ?? '127.0.0.1'),
            port: (int) ($data['port'] ?? 6379),
            timeout: (float) ($data['timeout'] ?? 2.0),
            prefix: (string) ($data['prefix'] ?? 'tg:'),
            outboundQueue: (string) ($data['outbound_queue'] ?? 'tg-outbound-requests'),
            processorQueue: (string) ($data['processor_queue'] ?? 'tg-processor-jobs'),
            blockTimeout: (int) ($data['block_timeout'] ?? 2),
        );
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'timeout' => $this->timeout,
            'prefix' => $this->prefix,
            'outbound_queue' => $this->outboundQueue,
            'processor_queue' => $this->processorQueue,
            'block_timeout' => $this->blockTimeout,
        ];
    }

    public static function fromOptions(array $options): self
    {
        return new self(
            host: (string)($options['redis-host'] ?? getenv('TG_LIB_REDIS_HOST') ?: '127.0.0.1'),
            port: (int)($options['redis-port'] ?? getenv('TG_LIB_REDIS_PORT') ?: 6379),
            timeout: (float)($options['redis-timeout'] ?? getenv('TG_LIB_REDIS_TIMEOUT') ?: 2.0),
            prefix: (string)($options['redis-prefix'] ?? getenv('TG_LIB_REDIS_PREFIX') ?: 'tg:'),
            outboundQueue: (string)($options['outbound-queue'] ?? getenv('TG_LIB_REDIS_OUTBOUND_QUEUE') ?: 'tg-outbound-requests'),
            processorQueue: (string)($options['processor-queue'] ?? getenv('TG_LIB_REDIS_PROCESSOR_QUEUE') ?: 'tg-processor-jobs'),
            blockTimeout: (int)($options['block-timeout'] ?? getenv('TG_LIB_REDIS_BLOCK_TIMEOUT') ?: 2),
        );
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function timeout(): float
    {
        return $this->timeout;
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function outboundQueue(): string
    {
        return $this->outboundQueue;
    }

    public function processorQueue(): string
    {
        return $this->processorQueue;
    }

    public function blockTimeout(): int
    {
        return $this->blockTimeout;
    }
}
