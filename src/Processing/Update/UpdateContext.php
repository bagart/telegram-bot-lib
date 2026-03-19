<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Update;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

final class UpdateContext
{
    public readonly int $receivedAt;

    /**
     * @param class-string<\BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract> $processor
     */
    public function __construct(
        public readonly TgApiTypeDTOContract $dto,
        public readonly string $processor,
        public readonly TgBotConfig $botConfig,
        public readonly ?string $executionKey,
        public readonly string $jobId = '',
        public readonly int $attempt = 0,
        public readonly ?string $source = null,
        ?int $receivedAt = null,
    ) {
        $this->receivedAt = $receivedAt ?? time();
    }

    public function __serialize(): array
    {
        return [
            'dto' => serialize($this->dto),
            'processor' => $this->processor,
            'botConfig' => serialize($this->botConfig),
            'executionKey' => $this->executionKey,
            'jobId' => $this->jobId,
            'attempt' => $this->attempt,
            'source' => $this->source,
            'receivedAt' => $this->receivedAt,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct(
            dto: unserialize($data['dto'], ['allowed_classes' => true]),
            processor: $data['processor'],
            botConfig: unserialize($data['botConfig'], ['allowed_classes' => true]),
            executionKey: $data['executionKey'],
            jobId: $data['jobId'],
            attempt: $data['attempt'],
            source: $data['source'],
            receivedAt: $data['receivedAt'],
        );
    }
}
