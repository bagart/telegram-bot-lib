<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\DbLogger;

use BAGArt\TelegramBot\Contracts\DbLogger\TgDbLoggerWrapperContract;
use BAGArt\TelegramBot\Contracts\TgApi\TgApiTypeDTOContract;
use BAGArt\TelegramBot\TgApi\Types\DTO\CallbackQueryTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use PDO;
use Throwable;

class TgDbPdoLogger implements TgDbLoggerWrapperContract
{
    private string $tableName = 'log_messages';

    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function log(TgApiTypeDTOContract $dto, array $extra = []): void
    {
        $data = $this->extractData($dto, $extra);

        $sql = sprintf(
            'INSERT INTO %s (
                tg_bot_id,
                action,
                message_id,
                dto_type,
                chat_id,
                from_id,
                from_username,
                text,
                data,
                created_at
            ) VALUES (
                :tg_bot_id,
                :action,
                :message_id,
                :dto_type,
                :chat_id,
                :from_id,
                :from_username,
                :text,
                :data,
                :created_at
            )',
            $this->tableName
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } catch (Throwable $e) {
        }
    }

    public function setTableName(string $tableName): void
    {
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
