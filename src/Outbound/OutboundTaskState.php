<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use JsonSerializable;
use RuntimeException;

/**
 * Mutable state of an outbound task.
 *
 * Accompanies immutable {@see OutboundTask} in the envelope ({@see OutboundEnvelope}),
 * accumulating processing progress: attempt number, status, last error.
 *
 * Serialized to queue together with the task (JSON), allowing the task to survive
 * movement between workers during retry.
 */
final class OutboundTaskState implements JsonSerializable
{
    public const string STATUS_PENDING = 'pending';

    public const string STATUS_IN_PROGRESS = 'in_progress';

    public const string STATUS_DELIVERED = 'delivered';

    public const string STATUS_BUSINESS_ERROR = 'business_error';

    /**
     * @param  string  $status  One of STATUS_*.
     * @param  int  $attempt  Current attempt number (0 = not yet processed).
     * @param  string|null  $lastError  Brief last error description (no sensitive data).
     * @param  array<string,mixed>  $errorContext  Additional context for the last error.
     */
    public function __construct(
        private string $status = self::STATUS_PENDING,
        private int $attempt = 0,
        private ?string $lastError = null,
        private ?array $errorContext = null,
    ) {
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAttempt(): int
    {
        return $this->attempt;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getErrorContext(): ?array
    {
        return $this->errorContext;
    }

    public function isTerminal(): bool
    {
        return $this->status === self::STATUS_DELIVERED || $this->status === self::STATUS_BUSINESS_ERROR;
    }

    public function markInProgress(): void
    {
        $this->status = self::STATUS_IN_PROGRESS;
    }

    public function markDelivered(): void
    {
        $this->status = self::STATUS_DELIVERED;
        $this->lastError = null;
        $this->errorContext = null;
    }

    /**
     * @param  array<string,mixed>|null  $context
     */
    public function markBusinessError(string $reason, ?array $context = null): void
    {
        $this->status = self::STATUS_BUSINESS_ERROR;
        $this->lastError = $reason;
        $this->errorContext = $context;
    }

    public function incrementAttempt(): int
    {
        return ++$this->attempt;
    }

    /**
     * Reset state for next retry: status → pending, error is preserved in context.
     *
     * @param  array<string,mixed>|null  $context
     */
    public function setRetryContext(string $reason, ?array $context = null): void
    {
        $this->status = self::STATUS_PENDING;
        $this->lastError = $reason;
        $this->errorContext = $context;
    }

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'attempt' => $this->attempt,
            'lastError' => $this->lastError,
            'errorContext' => $this->errorContext,
        ];
    }

    /**
     * @param  array<string,mixed>  $data  Result of jsonSerialize().
     *
     * @throws RuntimeException If the format is not recognized.
     */
    public static function fromArray(array $data): self
    {
        $schemaVersion = $data['schemaVersion'] ?? 1;

        return match ($schemaVersion) {
            1 => self::fromArrayV1($data),
            default => throw new RuntimeException("Unsupported OutboundTaskState schemaVersion: {$schemaVersion}"),
        };
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private static function fromArrayV1(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? self::STATUS_PENDING),
            attempt: (int) ($data['attempt'] ?? 0),
            lastError: isset($data['lastError']) ? (string) $data['lastError'] : null,
            errorContext: isset($data['errorContext']) ? (array) $data['errorContext'] : null,
        );
    }
}
