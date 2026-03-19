<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Outbound;

use BAGArt\TelegramBot\Configs\TgBotConfig;
use DateTimeImmutable;
use JsonSerializable;
use RuntimeException;

/**
 * Immutable payload of an outbound task.
 *
 * Stores only data needed for retrying in Telegram API, without behavior.
 * Fully serializable to JSON for the queue (Redis / in-memory).
 *
 * Mutations during processing (attempts, status, last error) live in
 * {@see OutboundTaskState} — the task remains immutable.
 */
final class OutboundTask implements JsonSerializable
{
    /**
     * @param  string  $id  Unique task identifier (bin2hex(random_bytes(16))).
     * @param  TgBotConfig  $botConfig  Bot config with token and bot ID.
     * @param  string  $dtoClass  Fully-qualified DTO class name (implements TgApiMethodDTOContract).
     * @param  array<string,mixed>  $dtoData  Raw DTO representation via TgApiDTOMapper::toArray().
     * @param  TaskPriority  $priority  Queue priority (see TaskPriority).
     * @param  string|null  $orderingKey  Strict ordering key (chat_id:session_id …); null = broadcast.
     * @param  DateTimeImmutable  $createdAt  Task creation time (for FIFO + expiry check).
     * @param  int  $schemaVersion  Format version — for future migrations (currently v1).
     */
    public function __construct(
        public readonly string $id,
        public readonly TgBotConfig $botConfig,
        public readonly string $dtoClass,
        public readonly array $dtoData,
        public readonly TaskPriority $priority = TaskPriority::Normal,
        public readonly ?string $orderingKey = null,
        public readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        public readonly int $schemaVersion = 1,
    ) {
    }

    /**
     * Calculates the task age in seconds since creation.
     *
     * @param  int  $now  Current Unix time in seconds.
     */
    public function age(int $now): int
    {
        return max(0, $now - $this->createdAt->getTimestamp());
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'botConfig' => [
                'token' => $this->botConfig->token,
                'botId' => $this->botConfig->botId,
            ],
            'dtoClass' => $this->dtoClass,
            'dtoData' => $this->dtoData,
            'priority' => $this->priority->value,
            'orderingKey' => $this->orderingKey,
            'createdAt' => $this->createdAt->format(DateTimeImmutable::ATOM),
            'schemaVersion' => $this->schemaVersion,
        ];
    }

    /**
     * @param  array<string,mixed>  $data  Result of jsonSerialize().
     *
     * @throws RuntimeException If format is not recognized (malformed JSON should be caught by the caller).
     */
    public static function fromJson(array $data): self
    {
        $schemaVersion = $data['schemaVersion'] ?? 1;

        return match ($schemaVersion) {
            1 => self::fromJsonV1($data),
            default => throw new RuntimeException("Unsupported OutboundTask schemaVersion: {$schemaVersion}"),
        };
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private static function fromJsonV1(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            botConfig: new TgBotConfig(
                token: (string) ($data['botConfig']['token'] ?? ''),
                botId: isset($data['botConfig']['botId']) ? (string) $data['botConfig']['botId'] : null,
            ),
            dtoClass: (string) $data['dtoClass'],
            dtoData: (array) ($data['dtoData'] ?? []),
            priority: TaskPriority::from((int) ($data['priority'] ?? TaskPriority::Normal->value)),
            orderingKey: isset($data['orderingKey']) ? (string) $data['orderingKey'] : null,
            createdAt: new DateTimeImmutable((string) ($data['createdAt'] ?? 'now')),
            schemaVersion: 1,
        );
    }
}
