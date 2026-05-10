<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Queue;

use InvalidArgumentException;

final class TgOutboundResponseDTO
{
    public function __construct(
        public readonly string $requestId,
        public readonly bool $success,
        public readonly mixed $result = null,
        public readonly ?string $error = null,
        public readonly ?int $errorCode = null,
        public readonly ?int $retryAfter = null,
        public readonly ?string $responseQueue = null,
        public readonly int $completedAt = 0,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->requestId === '') {
            throw new InvalidArgumentException(
                'requestId must not be empty.'
            );
        }

        if ($this->completedAt <= 0) {
            throw new InvalidArgumentException(
                'completedAt must be greater than zero.'
            );
        }

        if ($this->success) {
            if ($this->error !== null) {
                throw new InvalidArgumentException(
                    'Successful response cannot contain error message.'
                );
            }

            if ($this->errorCode !== null) {
                throw new InvalidArgumentException(
                    'Successful response cannot contain errorCode.'
                );
            }

            if ($this->retryAfter !== null) {
                throw new InvalidArgumentException(
                    'Successful response cannot contain retryAfter.'
                );
            }
        }

        if (!$this->success) {
            if ($this->result !== null) {
                throw new InvalidArgumentException(
                    'Failed response cannot contain result payload.'
                );
            }

            if ($this->error !== null && $this->error === '') {
                throw new InvalidArgumentException(
                    'Error message cannot be an empty string.'
                );
            }

            if ($this->retryAfter !== null && $this->retryAfter <= 0) {
                throw new InvalidArgumentException(
                    'retryAfter must be greater than zero.'
                );
            }
        }

        if (
            $this->responseQueue !== null
            && $this->responseQueue === ''
        ) {
            throw new InvalidArgumentException(
                'responseQueue cannot be an empty string.'
            );
        }
    }
}
