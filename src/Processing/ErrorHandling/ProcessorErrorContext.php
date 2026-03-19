<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\ErrorHandling;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use BAGArt\TelegramBot\Contracts\Processing\Processors\TgTypeDTOProcessorContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;

final readonly class ProcessorErrorContext
{
    public function __construct(
        public \Throwable $exception,
        public TgTypeDTOProcessorContract $processor,
        public TgApiTypeDTOContract $dto,
        public TgBotConfig $botConfig,
        public int $attempt = 1,
        public ?string $action = null,
    ) {
    }

    public function nextAttempt(): self
    {
        return new self(
            exception: $this->exception,
            processor: $this->processor,
            dto: $this->dto,
            botConfig: $this->botConfig,
            attempt: $this->attempt + 1,
            action: $this->action,
        );
    }
}
