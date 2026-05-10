<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use BAGArt\TelegramBot\Contracts\TgApi\TgApiMethodDTOContract;
use InvalidArgumentException;

final class TgOutboundRequestDTO
{
    public function __construct(
        public readonly string $requestId,
        public readonly string $token,
        public readonly TgApiMethodDTOContract $dto,
        public readonly TgRequestExecutionConfig $executionConfig,
        public readonly ?string $responseQueue = null,
        public int $createdAt = 0,
    ) {
        $this->createdAt = $createdAt ?: time();

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->requestId === '') {
            throw new InvalidArgumentException(
                'requestId must not be empty.'
            );
        }

        if ($this->token === '') {
            throw new InvalidArgumentException(
                'token must not be empty.'
            );
        }

        if ($this->createdAt <= 0) {
            throw new InvalidArgumentException(
                'createdAt must be greater than zero.'
            );
        }

        if (
            $this->executionConfig->mode === TgRequestExecutionConfig::MODE_SYNC
            && ($this->responseQueue === null || $this->responseQueue === '')
        ) {
            throw new InvalidArgumentException(
                'responseQueue is required for sync execution mode.'
            );
        }

        if (
            $this->executionConfig->mode === TgRequestExecutionConfig::MODE_ASYNC
            && $this->responseQueue !== null
            && $this->responseQueue === ''
        ) {
            throw new InvalidArgumentException(
                'responseQueue cannot be an empty string.'
            );
        }
    }
}
