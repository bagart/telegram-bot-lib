<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use DateTimeImmutable;
use JsonSerializable;
use RuntimeException;

/**
 * Structured Dead Letter Queue entry.
 *
 * Stores a full snapshot of the task and its state at failure time, the reason,
 * and a redelivery counter (guards against infinite loop on --retry).
 *
 * Serialized into the DLQ queue channel (tg-dlq:{botId}) as JSON.
 */
final class DeadLetterEntry implements JsonSerializable
{
    /** Maximum number of redeliveries for a single DLQ entry. */
    public const int MAX_REDELIVERIES = 3;

    public const int SCHEMA_VERSION = 1;

    /**
     * @param  string  $id  Entry ID (= task.id at first failure).
     * @param  string  $reason  Reason for landing in DLQ (expired / max_attempts / bad_request …).
     * @param  string  $failedAt  ISO 8601 failure timestamp.
     * @param  array<string,mixed>  $originalTask  OutboundTask::jsonSerialize() at failure.
     * @param  array<string,mixed>  $originalState  OutboundTaskState::jsonSerialize() at failure.
     * @param  int  $redeliveryCount  How many times this entry has been retried via --retry.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $reason,
        public readonly string $failedAt,
        public readonly array $originalTask,
        public readonly array $originalState,
        public int $redeliveryCount = 0,
    ) {
    }

    /**
     * Create entry from envelope: snapshots task+state at failure.
     */
    public static function fromEnvelope(OutboundEnvelope $envelope, string $reason): self
    {
        return new self(
            id: $envelope->task->id,
            reason: $reason,
            failedAt: (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            originalTask: $envelope->task->jsonSerialize(),
            originalState: $envelope->state->jsonSerialize(),
            redeliveryCount: 0,
        );
    }

    /**
     * Restore envelope for retry.
     * Resets attempt count and status — task is ready for processing again.
     */
    public function restoreEnvelope(): OutboundEnvelope
    {
        $task = OutboundTask::fromJson($this->originalTask);
        $state = OutboundTaskState::fromArray($this->originalState);

        // Reset to fresh attempt: state is "as new", but error history is preserved in context.
        return new OutboundEnvelope(
            task: $task,
            state: new OutboundTaskState(
                status: OutboundTaskState::STATUS_PENDING,
                attempt: 0,
                lastError: $this->reason,
                errorContext: ['redelivered_from_dlq' => $this->id, 'redelivery' => $this->redeliveryCount + 1],
            ),
        );
    }

    /**
     * Whether the entry can be redelivered from DLQ (guards against infinite loop).
     */
    public function canRedeliver(int $max = self::MAX_REDELIVERIES): bool
    {
        return $this->redeliveryCount < $max;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'failedAt' => $this->failedAt,
            'originalTask' => $this->originalTask,
            'originalState' => $this->originalState,
            'redeliveryCount' => $this->redeliveryCount,
            'schemaVersion' => self::SCHEMA_VERSION,
        ];
    }

    /**
     * @param  array<string,mixed>  $data
     *
     * @throws RuntimeException If the format is not recognized.
     */
    public static function fromJson(array $data): self
    {
        $schemaVersion = $data['schemaVersion'] ?? 1;

        return match ($schemaVersion) {
            1 => self::fromJsonV1($data),
            default => throw new RuntimeException("Unsupported DeadLetterEntry schemaVersion: {$schemaVersion}"),
        };
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private static function fromJsonV1(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            reason: (string) $data['reason'],
            failedAt: (string) ($data['failedAt'] ?? (new DateTimeImmutable())->format(DateTimeImmutable::ATOM)),
            originalTask: (array) $data['originalTask'],
            originalState: (array) $data['originalState'],
            redeliveryCount: (int) ($data['redeliveryCount'] ?? 0),
        );
    }
}
