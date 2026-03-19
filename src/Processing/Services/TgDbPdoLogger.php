<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Services;

use BAGArt\AsyncKernel\Wrappers\ASKLogWrapper;
use BAGArt\TelegramBot\Contracts\Processing\TgDbLoggerContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\Exceptions\TgBotConfigurationException;
use BAGArt\TelegramBot\TgApi\Types\DTO\CallbackQueryTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use PDO;
use Throwable;

class TgDbPdoLogger implements TgDbLoggerContract
{
    private string $tableName = 'log_messages';

    public function __construct(
        private readonly PDO $pdo,
        private readonly ?ASKLogWrapper $logger = null,
    ) {
    }

    public function log(TgApiTypeDTOContract $dto, array $extra = []): void
    {
        $data = $this->extractData($dto, $extra);

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->tableName} "
                .'('.implode(',', array_keys($data)).')'
                .' VALUES '
                .'(:'.implode(', :', array_keys($data)).')'
            );
            $stmt->execute($data);
        } catch (Throwable $e) {
            $this->logger?->error('Error on write DB log by '.static::class . ". {$e->getMessage()}");
        }
    }

    public function setTableName(string $tableName): void
    {
        if (!preg_match('~^[a-zA-Z][a-zA-Z0-9\-_]+$~', $tableName)) {
            throw new TgBotConfigurationException("Wrong table name for log: $tableName");
        }

        $this->tableName = $tableName;
    }

    private function extractData(TgApiTypeDTOContract $dto, array $extra): array
    {
        $chatId = null;
        $fromId = null;
        $fromUsername = null;
        $text = null;
        $action = 'unknown';
        $messageId = null;

        if ($dto instanceof MessageTypeDTO) {
            $chatId = $dto->chat->id;
            $fromId = $dto->from?->id;
            $fromUsername = $dto->from?->username ?? '';
            $text = $dto->text ?? $dto->caption ?? '';
            $action = 'message';
            $messageId = $dto->messageId ?? null;
        } elseif ($dto instanceof CallbackQueryTypeDTO) {
            $chatId = $dto->message?->chat->id;
            $fromId = $dto->from->id;
            $fromUsername = $dto->from->username ?? '';
            $text = $dto->data ?? '';
            $action = 'callback';
            $messageId = $dto->message?->messageId ?? null;
        }

        return [
            'tg_bot_id' => $extra['bot_id'] ?? '',
            'action' => $action,
            'message_id' => $messageId,
            'dto_type' => $dto::class,
            'chat_id' => $chatId,
            'from_id' => $fromId,
            'from_username' => $fromUsername,
            'text' => mb_substr($text ?? '', 0, 500),
            'data' => json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => date('Y-m-d\TH:i:s'),
        ];
    }
}
